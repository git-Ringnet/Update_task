<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\ProjectMemberService;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with([
            'user:id,name,avatar',
            'project:id,customer_id,title',
            'project.customer:id,name',
        ])->orderByDesc('created_at');

        $user = auth()->user();
        $isSpecificProject = ($request->has('project_id') && !empty($request->project_id)) || $request->has('task_id');

        if ($user->isSystemAdmin()) {
            // System Admin has global visibility across all projects
        } elseif ($user->is_admin) {
            $query->whereHas('project', fn ($q) => $q->where('hidden_from_admin', false));
        } else {
            if ($isSpecificProject) {
                // When explicitly viewing a specific project/task, all authorized members see full discussion history
                $query->whereHas('project', fn ($q) => $q->visibleTo($user));
            } else {
                // On the team activity feed: newly added members only see comments created after they were added to the project
                $query->where(function ($q) use ($user) {
                    $q->where('comments.user_id', $user->id)
                        ->orWhereHas('project', function ($pq) use ($user) {
                            $pq->where('created_by', $user->id)
                                ->orWhere('lead_id', $user->id);
                        })
                        ->orWhereExists(function ($sub) use ($user) {
                            $sub->selectRaw(1)
                                ->from('project_members')
                                ->whereColumn('project_members.project_id', 'comments.project_id')
                                ->where('project_members.user_id', $user->id)
                                ->whereColumn('project_members.created_at', '<=', 'comments.created_at');
                        });
                });
            }
        }

        // Privacy filter: Private comments are only visible to system-admin, sender, and private recipients
        if (!$user->isSystemAdmin()) {
            $query->where(function ($privacyQuery) use ($user) {
                $privacyQuery->where('comments.is_private', false)
                    ->orWhereNull('comments.is_private')
                    ->orWhere('comments.user_id', $user->id)
                    ->orWhereJsonContains('comments.private_user_ids', (int) $user->id)
                    ->orWhereJsonContains('comments.private_user_ids', (string) $user->id);
            });
        }

        $projectIds = collect($request->input('project_ids', []))
            ->map(fn ($id) => filter_var($id, FILTER_VALIDATE_INT))
            ->filter()
            ->unique()
            ->values();

        if ($projectIds->isNotEmpty()) {
            $query->whereIn('project_id', $projectIds);
        } elseif ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        // Fast polling path: only return rows created after the newest comment
        // already present in the browser. This avoids repeatedly transferring
        // the full feed (including legacy inline image data).
        $afterId = $request->integer('after_id');
        if ($afterId > 0) {
            $query->where('comments.id', '>', $afterId);
        }

        // Backward pagination path: load older comments when scrolling up
        $beforeDate = $request->get('before_date');
        $beforeId = $request->integer('before_id');
        if ($beforeDate && $beforeId > 0) {
            $query->where(function ($q) use ($beforeDate, $beforeId) {
                $q->where('comments.created_at', '<', $beforeDate)
                  ->orWhere(function ($sub) use ($beforeDate, $beforeId) {
                      $sub->where('comments.created_at', '=', $beforeDate)
                          ->where('comments.id', '<', $beforeId);
                  });
            });
        } elseif ($beforeDate) {
            $query->where('comments.created_at', '<', $beforeDate);
        } elseif ($beforeId > 0) {
            $query->where('comments.id', '<', $beforeId);
        }

        $days = $request->integer('days');
        if ($days > 0) {
            $query->where('created_at', '>=', Carbon::now()->subDays(min($days, 90)));
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

    public function show($id)
    {
        // Strip out any non-numeric prefix like 'comment-' or 'task-'
        $commentId = is_numeric($id) ? (int) $id : (int) preg_replace('/\D/', '', (string) $id);
        abort_unless($commentId > 0, 404, 'Không tìm thấy bình luận.');

        $comment = Comment::with([
            'user:id,name,avatar',
            'project:id,customer_id,title',
            'project.customer:id,name',
        ])->find($commentId);

        if (!$comment) {
            $comment = Comment::with([
                'user:id,name,avatar',
                'project:id,customer_id,title',
                'project.customer:id,name',
            ])->where('task_id', $commentId)->first();
        }

        if (!$comment) {
            $task = Task::with([
                'creator:id,name,avatar',
                'project:id,customer_id,title',
                'project.customer:id,name',
            ])->find($commentId);

            if ($task) {
                $comment = Comment::firstOrCreate(
                    ['task_id' => $task->id],
                    [
                        'project_id' => $task->project_id,
                        'user_id' => $task->created_by,
                        'content' => $task->title,
                        'is_private' => (bool) $task->is_private,
                        'private_user_ids' => $task->private_user_ids,
                        'created_at' => $task->created_at,
                        'updated_at' => $task->updated_at,
                    ]
                );
                $comment->load([
                    'user:id,name,avatar',
                    'project:id,customer_id,title',
                    'project.customer:id,name',
                ]);
            }
        }

        abort_unless($comment, 404, 'Không tìm thấy bình luận.');

        $user = auth()->user();
        abort_unless($comment->project?->isVisibleTo($user), 403, 'Bạn không có quyền xem bình luận này.');

        // Privacy check: only sender, recipients, and system-admin can view private comment
        if ($comment->is_private && !$user->isSystemAdmin()) {
            $isSender = (int) $comment->user_id === (int) $user->id;
            $isRecipient = is_array($comment->private_user_ids) && (
                in_array((int) $user->id, $comment->private_user_ids, true) ||
                in_array((string) $user->id, $comment->private_user_ids, true)
            );
            abort_unless($isSender || $isRecipient, 403, 'Bạn không có quyền xem bình luận riêng tư này.');
        }

        $comment->setAttribute('project_title', $comment->project?->title);
        return response()->json($comment);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'content' => 'required|string',
            'type' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'private_user_ids' => 'nullable|array',
            'private_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
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
        // The project is already loaded here; supplying its health prevents
        // Comment's creating hook from querying the same project again.
        $validated['project_health'] = $project->health;

        $privateUserIds = app(ProjectMemberService::class)->extractPrivateMentionUserIds(
            $validated['content'],
            $validated['private_user_ids'] ?? []
        );
        if (!empty($privateUserIds)) {
            $validated['is_private'] = true;
            $validated['private_user_ids'] = array_values(array_unique($privateUserIds));
        }

        app(ProjectMemberService::class)->addMentionedMembers($project, $validated['content'], array_merge($taggedUserIds, $privateUserIds));

        $comment = Comment::create($validated);
        $comment->load([
            'user:id,name,avatar',
            'project:id,customer_id,title',
            'project.customer:id,name',
        ]);
        $comment->setAttribute('project_title', $comment->project?->title);

        Project::where('id', $comment->project_id)->update(['last_activity_at' => Carbon::now()]);

        return response()->json($comment, 201);
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();
        $canEdit = $user->is_system_admin || $user->is_admin || (int) $comment->user_id === (int) $user->id;
        abort_unless($canEdit, 403, 'Bạn không có quyền chỉnh sửa bình luận này.');

        $validated = $request->validate([
            'content' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_private' => 'nullable|boolean',
            'private_user_ids' => 'nullable|array',
            'private_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
            'tagged_user_ids' => 'nullable|array',
            'tagged_user_ids.*' => Rule::exists('users', 'id')->where('is_admin', 0),
        ]);

        $projectId = $validated['project_id'] ?? $comment->project_id;
        $project = Project::findOrFail($projectId);
        $taggedUserIds = $validated['tagged_user_ids'] ?? [];
        unset($validated['tagged_user_ids']);

        $privateUserIds = app(ProjectMemberService::class)->extractPrivateMentionUserIds(
            $validated['content'],
            $validated['private_user_ids'] ?? ($comment->private_user_ids ?? [])
        );

        $updateData = ['content' => $validated['content']];
        if (!empty($projectId)) {
            $updateData['project_id'] = $projectId;
        }
        if (!empty($privateUserIds)) {
            $updateData['is_private'] = true;
            $updateData['private_user_ids'] = array_values(array_unique($privateUserIds));
        } else {
            $updateData['is_private'] = $validated['is_private'] ?? false;
            $updateData['private_user_ids'] = $validated['private_user_ids'] ?? null;
        }

        app(ProjectMemberService::class)->addMentionedMembers($project, $validated['content'], array_merge($taggedUserIds, $privateUserIds));

        $oldContent = $comment->getOriginal('content');
        $oldProjectId = $comment->getOriginal('project_id');
        $comment->update($updateData);

        // Sync associated Task
        $task = null;
        if ($comment->task_id) {
            $task = Task::find($comment->task_id);
        }
        if (!$task) {
            $task = Task::where(function ($q) use ($comment, $oldProjectId) {
                    $q->where('project_id', $comment->project_id)
                      ->orWhere('project_id', $oldProjectId);
                })
                ->where('created_by', $comment->user_id)
                ->where(function ($q) use ($oldContent, $comment) {
                    $q->where('title', $oldContent)
                      ->orWhere('title', $comment->content);
                })
                ->first();
            if ($task && !$comment->task_id) {
                $comment->task_id = $task->id;
                $comment->saveQuietly();
            }
        }

        if ($task) {
            $taskUpdateData = [
                'title' => $comment->content,
                'is_private' => (bool) ($comment->is_private ?? false),
                'private_user_ids' => $comment->private_user_ids,
            ];
            if (!empty($projectId)) {
                $taskUpdateData['project_id'] = $projectId;
                if ($task->milestone_id) {
                    $milestoneValid = Milestone::where('id', $task->milestone_id)->where('project_id', $projectId)->exists();
                    if (!$milestoneValid) {
                        $taskUpdateData['milestone_id'] = null;
                    }
                }
            }
            $task->update($taskUpdateData);
        }

        $comment->load([
            'user:id,name,avatar',
            'project:id,customer_id,title',
            'project.customer:id,name',
        ]);
        $comment->setAttribute('project_title', $comment->project?->title);

        return response()->json($comment);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();
        $canDelete = $user->is_system_admin || $user->is_admin || (int) $comment->user_id === (int) $user->id;
        abort_unless($canDelete, 403, 'Bạn không có quyền xóa bình luận này.');

        // Delete associated task and attachments if this comment represents a project task/update
        if ($comment->task_id) {
            $task = Task::find($comment->task_id);
            if ($task) {
                Attachment::where('task_id', $task->id)->delete();
                $task->delete();
            }
        } else {
            // Also check for matching task by project, user, and content if task_id wasn't populated
            $matchingTask = Task::where('project_id', $comment->project_id)
                ->where('title', $comment->content)
                ->where('created_by', $comment->user_id)
                ->first();
            if ($matchingTask) {
                Attachment::where('task_id', $matchingTask->id)->delete();
                $matchingTask->delete();
            }
        }

        $comment->delete();

        return response()->json(['message' => 'Đã xóa bình luận']);
    }
}
