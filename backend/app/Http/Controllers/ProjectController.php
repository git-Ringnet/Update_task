<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Project::with(['customer', 'lead', 'tasks', 'milestones.tasks', 'latestComment.user'])
            ->withCount(['tasks', 'comments']);

        if ($userId) {
            $query->select('projects.*')
                ->leftJoin('pinned_projects', function ($join) use ($userId) {
                    $join->on('pinned_projects.project_id', '=', 'projects.id')
                         ->where('pinned_projects.user_id', '=', $userId);
                })
                ->orderByRaw('CASE WHEN pinned_projects.id IS NOT NULL THEN 1 ELSE 0 END DESC');
        }

        $query->orderBy('sort_order', 'asc')
              ->orderBy('last_activity_at', 'desc');

        // Filter by tracking_status (NOT health)
        if ($request->has('tracking_status')) {
            $query->where('tracking_status', $request->tracking_status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->get()->map(function ($p) use ($userId) {
            return $p->applyPinnedStateForUser($userId);
        });

        // Calculate counts based on tracking_status (NOT health)
        $counts = [
            'following' => Project::where('tracking_status', 'following')->count(),
            'not_following' => Project::where('tracking_status', 'not_following')->count(),
            'completed' => Project::where('tracking_status', 'completed')->count(),
        ];

        return response()->json([
            'projects' => $projects,
            'counts' => $counts,
        ]);
    }

    public function show($id)
    {
        $project = Project::with(['customer', 'lead', 'tasks.assignee', 'comments.user', 'milestones.creator', 'milestones.tasks.assignee'])->findOrFail($id);
        $project->applyPinnedStateForUser(auth()->id());
        return response()->json($project);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'lead_id' => 'nullable|exists:users,id',
            'health' => 'required|in:green,yellow,red',
            'is_pinned' => 'boolean',
            'tracking_status' => 'sometimes|in:following,not_following,completed',
        ]);

        // Map health to tracking_status only if not provided
        if (!isset($validated['tracking_status'])) {
            if ($validated['health'] === 'yellow') {
                $validated['tracking_status'] = 'following';
            } elseif ($validated['health'] === 'red') {
                $validated['tracking_status'] = 'not_following';
            } elseif ($validated['health'] === 'green') {
                $validated['tracking_status'] = 'completed';
            }
        }

        $validated['last_activity_at'] = Carbon::now();

        $pinOnCreate = $validated['is_pinned'] ?? false;
        unset($validated['is_pinned']);

        $project = Project::create($validated);
        $project->load(['customer', 'lead']);

        $userId = auth()->id();
        if ($pinOnCreate && $userId) {
            \Illuminate\Support\Facades\DB::table('pinned_projects')->updateOrInsert(
                ['user_id' => $userId, 'project_id' => $project->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        $project->applyPinnedStateForUser($userId);

        // Log comment
        Comment::create([
            'project_id' => $project->id,
            'user_id' => auth()->id() ?? $request->user_id ?? $project->lead_id ?? \App\Models\User::first()->id ?? null,
            'content' => "Đã tạo dự án mới: {$project->title}",
            'type' => 'status_change',
        ]);

        return response()->json($project, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'title' => 'sometimes|string|max:255',
            'lead_id' => 'nullable|exists:users,id',
            'health' => 'sometimes|in:green,yellow,red',
            'is_pinned' => 'boolean',
            'tracking_status' => 'sometimes|in:following,not_following,completed',
        ]);

        $project = Project::findOrFail($id);
        $oldHealth = $project->health;

        // ❌ REMOVED: Do NOT auto-sync health and tracking_status
        // They are now completely independent fields

        // Only update last_activity_at if it's a meaningful change (not just status/health/pin toggle)
        $shouldUpdateActivity = isset($validated['title']) 
            || isset($validated['customer_id']) 
            || isset($validated['lead_id']);
        
        if ($shouldUpdateActivity) {
            $validated['last_activity_at'] = Carbon::now();
        }

        $userId = auth()->id();
        $pinState = null;
        if (array_key_exists('is_pinned', $validated)) {
            $pinState = (bool) $validated['is_pinned'];
            unset($validated['is_pinned']);
        }

        $project->update($validated);

        if ($pinState !== null && $userId) {
            if ($pinState) {
                \Illuminate\Support\Facades\DB::table('pinned_projects')->updateOrInsert(
                    ['user_id' => $userId, 'project_id' => $id],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            } else {
                \Illuminate\Support\Facades\DB::table('pinned_projects')
                    ->where('user_id', $userId)
                    ->where('project_id', $id)
                    ->delete();
            }
        }

        $project->load(['customer', 'lead']);
        $project->applyPinnedStateForUser($userId);

        if (isset($validated['health']) && $validated['health'] !== $oldHealth) {
            $statusNames = [
                'yellow' => 'Đang theo (Vàng)',
                'red' => 'Không theo (Đỏ)',
                'green' => 'Hoàn thành (Xanh)',
            ];
            Comment::create([
                'project_id' => $project->id,
                'user_id' => auth()->id() ?? $request->user_id ?? $project->lead_id ?? \App\Models\User::first()->id ?? null,
                'content' => "Đã chuyển trạng thái dự án sang [{$statusNames[$project->health]}]",
                'type' => 'health_update',
            ]);
        }

        return response()->json($project);
    }

    public function updateHealth(Request $request, $id)
    {
        $request->validate([
            'health' => 'required|in:green,yellow,red',
        ]);

        $project = Project::findOrFail($id);
        $oldHealth = $project->health;
        $project->health = $request->health;

        // ❌ REMOVED: Do NOT sync tracking_status when updating health
        // Health and tracking_status are now independent

        // ❌ REMOVED: Do NOT update last_activity_at for health changes
        // This prevents the project from jumping to top of the list
        // $project->last_activity_at = Carbon::now();
        
        $project->save();

        // Create log comment for health update
        $statusNames = [
            'yellow' => 'Đang theo (Vàng)',
            'red' => 'Không theo (Đỏ)',
            'green' => 'Hoàn thành (Xanh)',
        ];

        Comment::create([
            'project_id' => $project->id,
            'user_id' => auth()->id() ?? $request->user_id ?? $project->lead_id ?? \App\Models\User::first()->id ?? null,
            'content' => "Đã chuyển trạng thái dự án sang [{$statusNames[$project->health]}]",
            'type' => 'health_update',
        ]);

        $project->load(['customer', 'lead']);

        return response()->json([
            'message' => 'Cập nhật health status thành công',
            'project' => $project,
        ]);
    }

    public function togglePin($id, Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Project::findOrFail($id);

        $currentlyPinned = \Illuminate\Support\Facades\DB::table('pinned_projects')
            ->where('user_id', $userId)
            ->where('project_id', $id)
            ->exists();

        $newPinnedState = !$currentlyPinned;

        if ($newPinnedState) {
            \Illuminate\Support\Facades\DB::table('pinned_projects')->updateOrInsert(
                ['user_id' => $userId, 'project_id' => $id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        } else {
            \Illuminate\Support\Facades\DB::table('pinned_projects')
                ->where('user_id', $userId)
                ->where('project_id', $id)
                ->delete();
        }

        $project = Project::with(['customer', 'lead'])->findOrFail($id);
        $project->setAttribute('is_pinned', $newPinnedState);

        return response()->json([
            'message' => 'Cập nhật ghim thành công',
            'project' => $project,
            'is_pinned' => $newPinnedState,
        ]);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        $hasRealComments = $project->comments()
            ->where('content', 'not like', 'Đã tạo dự án mới%')
            ->exists();

        if ($project->tasks()->exists() || $hasRealComments) {
            return response()->json([
                'message' => 'Không thể xóa dự án đã có cập nhật hoặc nhiệm vụ.'
            ], 422);
        }

        $project->delete();

        return response()->json(['message' => 'Đã xóa dự án']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ]);

        foreach ($request->project_ids as $index => $id) {
            Project::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Cập nhật thứ tự dự án thành công']);
    }
}
