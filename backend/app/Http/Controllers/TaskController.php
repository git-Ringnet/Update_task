<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project.customer', 'assignee', 'creator'])
            ->orderBy('created_at', 'desc');

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
            'assignee_id' => 'nullable|exists:users,id',
            'created_by' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create($validated);
        $task->load(['project', 'assignee', 'creator']);

        // Update project last activity
        Project::where('id', $task->project_id)->update(['last_activity_at' => Carbon::now()]);

        // Create notification comment
        Comment::create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => auth()->id() ?? $task->created_by ?? 1,
            'content' => "Đã tạo công việc: {$task->title}",
            'type' => 'status_change',
        ]);

        return response()->json($task, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task = Task::findOrFail($id);
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
            'assignee_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $task = Task::findOrFail($id);
        $task->update($validated);
        $task->load(['project', 'assignee']);

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Đã xóa công việc']);
    }
}
