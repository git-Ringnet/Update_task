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

        $user = auth()->user();
        $query->whereHas('project', fn ($q) => $q->visibleTo($user));

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        } elseif (!$user->is_admin && !$user->isSystemAdmin()) {
            // The activity feed is an inbox, not the project's history. A member
            // only starts seeing project updates from the moment they join. The
            // project-specific endpoint deliberately remains unfiltered so its
            // detail page can still provide the full project context.
            $query->where(function ($visibleComments) use ($user) {
                $visibleComments
                    ->whereHas('project', function ($projects) use ($user) {
                        $projects->where('created_by', $user->id)
                            ->orWhere('lead_id', $user->id);
                    })
                    ->orWhereHas('project.members', function ($members) use ($user) {
                        $members->where('users.id', $user->id)
                            ->whereColumn('project_members.created_at', '<=', 'comments.created_at');
                    });
            });
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        // The dashboard only renders a short recent-activity list. Let callers
        // request that small window instead of serializing the full history.
        $limit = $request->integer('limit');
        if ($limit > 0) {
            $query->limit(min($limit, 100));
        }

        $comments = $query->get();
        // Keep a direct project title on every activity payload. Besides making
        // the feed simpler to render, this lets browser notifications reliably
        // name the project even if a client does not hydrate nested relations.
        $comments->each(function (Comment $comment) {
            $comment->setAttribute('project_title', $comment->project?->title);
        });
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
