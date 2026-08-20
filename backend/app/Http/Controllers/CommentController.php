<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\ProjectMemberService;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'project.customer'])->orderBy('created_at', 'desc');

        $query->whereHas('project', fn ($q) => $q->visibleTo(auth()->user()));

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        $comments = $query->get();
        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'content' => 'required|string',
            'type' => 'nullable|string',
            'tagged_user_ids' => 'nullable|array',
            'tagged_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless($project->isVisibleTo(auth()->user()), 403, 'Bạn không có quyền cập nhật dự án này.');
        if (!empty($validated['task_id'])) {
            abort_unless($project->tasks()->whereKey($validated['task_id'])->exists(), 422, 'Hoạt động không thuộc dự án.');
        }
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        unset($validated['tagged_user_ids']);
        $validated['user_id'] = auth()->id();

        $comment = Comment::create($validated);
        $comment->load('user');

        Project::where('id', $comment->project_id)->update(['last_activity_at' => Carbon::now()]);
        app(ProjectMemberService::class)->addMentionedMembers($project, $comment->content, $taggedUserIds);

        return response()->json($comment, 201);
    }
}
