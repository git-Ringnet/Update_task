<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\ProjectMemberService;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project.customer', 'assignee', 'creator'])
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
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless($project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền cập nhật dự án này.');
        if (!empty($validated['milestone_id'])) {
            abort_unless($project->milestones()->whereKey($validated['milestone_id'])->exists(), 422, 'Cột mốc không thuộc dự án.');
        }
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        unset($validated['tagged_user_ids']);
        $validated['created_by'] = auth()->id();
        $task = Task::create($validated);
        $task->load(['project', 'assignee', 'creator']);

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
        ]);

        $task = Task::findOrFail($id);
        $project = $task->project;
        abort_unless($project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền cập nhật hoạt động này.');
        if (!empty($validated['milestone_id'])) {
            abort_unless($project->milestones()->whereKey($validated['milestone_id'])->exists(), 422, 'Cột mốc không thuộc dự án.');
        }
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        unset($validated['tagged_user_ids']);
        $task->update($validated);
        $task->load(['project', 'assignee']);

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
        abort_unless($task->project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền xóa hoạt động này.');
        $task->delete();

        return response()->json(['message' => 'Đã xóa công việc']);
    }
}
