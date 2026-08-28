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
        $query = Task::with(['project.customer', 'assignee', 'creator', 'attachments'])
            ->orderBy('created_at', 'desc');

        $query->whereHas('project', fn ($q) => $q->visibleTo(auth()->user()));

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
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
        unset($validated['tagged_user_ids'], $validated['attachment_ids']);
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

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        // Create notification comment
        Comment::create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => auth()->id() ?? $task->created_by ?? 1,
            'content' => $task->title,
            'type' => 'status_change',
        ]);

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

        // Link newly uploaded attachments to this task
        if (!empty($attachmentIds)) {
            Attachment::whereIn('id', $attachmentIds)
                ->where('uploaded_by', auth()->id())
                ->whereNull('task_id')
                ->update(['task_id' => $task->id]);
        }

        $task->load(['project', 'assignee', 'attachments']);

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        // Update or create corresponding comment to show up at the top of Recent Activity
        $comment = Comment::where('task_id', $task->id)->where('type', 'status_change')->first();
        if ($comment) {
            $comment->update([
                'content' => $task->title,
                'user_id' => auth()->id() ?? $task->created_by ?? 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            Comment::create([
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'user_id' => auth()->id() ?? $task->created_by ?? 1,
                'content' => $task->title,
                'type' => 'status_change',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

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
        $task->delete();

        return response()->json(['message' => 'Đã xóa công việc']);
    }
}
