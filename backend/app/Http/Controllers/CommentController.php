<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'project.customer'])->orderBy('created_at', 'desc');

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
            'user_id' => 'nullable|exists:users,id',
            'content' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id() ?? $validated['user_id'] ?? \App\Models\User::first()->id ?? null;

        $comment = Comment::create($validated);
        $comment->load('user');

        Project::where('id', $comment->project_id)->update(['last_activity_at' => Carbon::now()]);

        return response()->json($comment, 201);
    }
}
