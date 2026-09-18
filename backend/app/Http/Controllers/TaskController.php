<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\ProjectMemberService;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::with(['project.customer', 'assignee', 'creator', 'attachments'])
            ->orderBy('created_at', 'desc');

        // Permission check: use subquery instead of correlated exists whereHas
        if ($user && !$user->isSystemAdmin()) {
            $visibleProjectIds = Project::visibleTo($user)->select('id');
            $query->whereIn('project_id', $visibleProjectIds);
        }

        // Filter by specific project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by exact status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Exclude specific status (e.g. exclude_status=done or status_not=done)
        if ($request->filled('exclude_status')) {
            $query->where('status', '!=', $request->exclude_status);
        } elseif ($request->filled('status_not')) {
            $query->where('status', '!=', $request->status_not);
        }

        // Filter tasks with due_date
        if ($request->boolean('has_due_date')) {
            $query->whereNotNull('due_date');
        }

        // Filter by due_date from date
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        // Special mode shortcuts
        if ($request->get('view_mode') === 'schedule' || $request->get('mode') === 'schedule') {
            $query->whereNotNull('due_date')
                  ->where('status', '!=', 'done');
        }

        if ($request->filled('limit')) {
            $query->limit((int) $request->limit);
        }

        $tasks = $query->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'assignee_id' => ['nullable', Rule::exists('users', 'id')->where('is_admin', 0)],
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable',
            'comment_id' => 'nullable|integer|exists:comments,id',
            'health' => 'nullable|string',
            'tagged_user_ids' => 'nullable|array',
            'tagged_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
            'attachment_ids' => 'nullable|array',
            'attachment_ids.*' => 'integer|exists:attachments,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless($project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền cập nhật dự án này.');
        if (!empty($validated['milestone_id'])) {
            abort_unless($project->milestones()->whereKey($validated['milestone_id'])->exists(), 422, 'Cột mốc không thuộc dự án.');
        }
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        $attachmentIds = $validated['attachment_ids'] ?? [];
        $commentId = $validated['comment_id'] ?? null;
        unset($validated['tagged_user_ids'], $validated['attachment_ids'], $validated['comment_id']);
        $validated['created_by'] = auth()->id();
        $task = Task::create($validated);

        // Link uploaded attachments to this task
        if (!empty($attachmentIds)) {
            Attachment::whereIn('id', $attachmentIds)
                ->where('uploaded_by', auth()->id())
                ->whereNull('task_id')
                ->update(['task_id' => $task->id]);
        }

        $task->load(['project', 'assignee', 'creator', 'attachments']);

        // Link to existing comment if created from an existing chat, otherwise create a new comment for activity feed
        $existingComment = null;
        if (!empty($commentId)) {
            $existingComment = Comment::where('id', $commentId)->where('project_id', $task->project_id)->first();
            if ($existingComment) {
                $existingComment->update(['task_id' => $task->id]);
            }
        }

        if (!$existingComment) {
            Comment::create([
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'user_id' => $task->created_by ?? auth()->id(),
                'content' => $task->title,
                'type' => 'comment',
                'project_health' => $task->health ?? $project->health,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ]);
        }

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        app(ProjectMemberService::class)->addMentionedMembers(
            $project,
            $task->title,
            array_filter(array_merge($taggedUserIds, [$task->assignee_id]))
        );

        return response()->json($task, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task = Task::findOrFail($id);
        abort_unless($task->project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền cập nhật hoạt động này.');
        $oldStatus = $task->status;
        $task->status = $request->status;
        $task->save();

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        Comment::create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => auth()->id() ?? $request->user_id ?? 1,
            'content' => "Đã chuyển công việc '{$task->title}' sang trạng thái [{$task->status}]",
            'type' => 'status_change',
        ]);

        $task->load(['project', 'assignee']);

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'milestone_id' => 'nullable|exists:milestones,id',
            'assignee_id' => ['nullable', Rule::exists('users', 'id')->where('is_admin', 0)],
            'title' => 'required|string',
            'due_date' => 'nullable',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'required|in:low,medium,high,urgent',
            'health' => 'nullable|string',
            'tagged_user_ids' => 'nullable|array',
            'tagged_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
            'attachment_ids' => 'nullable|array',
            'attachment_ids.*' => 'integer|exists:attachments,id',
        ]);

        $task = Task::findOrFail($id);
        $user = auth()->user();
        $canUpdate = $user->is_system_admin || $user->is_admin || (int) $task->created_by === (int) $user->id;
        abort_unless($canUpdate, 403, 'Bạn không có quyền cập nhật hoạt động này.');
        $project = $task->project;
        if (!empty($validated['milestone_id'])) {
            abort_unless($project->milestones()->whereKey($validated['milestone_id'])->exists(), 422, 'Cột mốc không thuộc dự án.');
        }
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        $attachmentIds = $validated['attachment_ids'] ?? [];
        unset($validated['tagged_user_ids'], $validated['attachment_ids']);
        $task->update($validated);
        $task->created_at = Carbon::now();
        $task->save();

        // Link newly uploaded attachments to this task
        if (!empty($attachmentIds)) {
            Attachment::whereIn('id', $attachmentIds)
                ->where('uploaded_by', auth()->id())
                ->whereNull('task_id')
                ->update(['task_id' => $task->id]);
        }

        // Sync associated comment
        $comment = Comment::where('task_id', $task->id)->first();
        if ($comment) {
            $comment->update([
                'content' => $task->title,
                'project_health' => $task->health ?? $project->health,
            ]);
        } else {
            Comment::create([
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'user_id' => $task->created_by ?? auth()->id(),
                'content' => $task->title,
                'type' => 'comment',
                'project_health' => $task->health ?? $project->health,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ]);
        }

        $task->load(['project', 'assignee', 'attachments']);

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        app(ProjectMemberService::class)->addMentionedMembers(
            $project,
            $task->title,
            array_filter(array_merge($taggedUserIds, [$task->assignee_id]))
        );

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $user = auth()->user();
        $canDelete = $user->is_system_admin || $user->is_admin || (int) $task->created_by === (int) $user->id;
        abort_unless($canDelete, 403, 'Bạn không có quyền xóa hoạt động này.');

        // Unlink associated comments and attachments so chat history remains intact
        Comment::where('task_id', $task->id)->update(['task_id' => null]);
        Attachment::where('task_id', $task->id)->update(['task_id' => null]);
        $task->delete();

        return response()->json(['message' => 'Đã xóa công việc']);
    }
}
