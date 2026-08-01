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
        $userId = auth()->id() ?? $request->user_id;

        $query = Project::with(['customer', 'lead', 'tasks'])
            ->withCount(['tasks', 'comments']);

        if ($userId) {
            $query->select('projects.*')
                ->leftJoin('pinned_projects', function ($join) use ($userId) {
                    $join->on('pinned_projects.project_id', '=', 'projects.id')
                         ->where('pinned_projects.user_id', '=', $userId);
                })
                ->orderByRaw('CASE WHEN pinned_projects.id IS NOT NULL THEN 1 ELSE 0 END DESC');
        }

        $query->orderBy('last_activity_at', 'desc');

        if ($request->has('tracking_status')) {
            $status = $request->tracking_status;
            if ($status === 'following') {
                $query->where('health', 'yellow');
            } elseif ($status === 'not_following') {
                $query->where('health', 'red');
            } elseif ($status === 'completed') {
                $query->where('health', 'green');
            }
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

        $projects = $query->get();

        // Calculate counts based on Health status
        $counts = [
            'following' => Project::where('health', 'yellow')->count(),
            'not_following' => Project::where('health', 'red')->count(),
            'completed' => Project::where('health', 'green')->count(),
        ];

        return response()->json([
            'projects' => $projects,
            'counts' => $counts,
        ]);
    }

    public function show($id)
    {
        $project = Project::with(['customer', 'lead', 'tasks.assignee', 'comments.user', 'milestones.creator'])->findOrFail($id);
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
        ]);

        // Map health to tracking_status
        if ($validated['health'] === 'yellow') {
            $validated['tracking_status'] = 'following';
        } elseif ($validated['health'] === 'red') {
            $validated['tracking_status'] = 'not_following';
        } elseif ($validated['health'] === 'green') {
            $validated['tracking_status'] = 'completed';
        }

        $validated['last_activity_at'] = Carbon::now();
        $project = Project::create($validated);
        $project->load(['customer', 'lead']);

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
        ]);

        $project = Project::findOrFail($id);
        $oldHealth = $project->health;

        if (isset($validated['health'])) {
            if ($validated['health'] === 'yellow') {
                $validated['tracking_status'] = 'following';
            } elseif ($validated['health'] === 'red') {
                $validated['tracking_status'] = 'not_following';
            } elseif ($validated['health'] === 'green') {
                $validated['tracking_status'] = 'completed';
            }
        }

        $validated['last_activity_at'] = Carbon::now();

        $project->update($validated);
        $project->load(['customer', 'lead']);

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

        // Sync tracking_status
        if ($request->health === 'yellow') {
            $project->tracking_status = 'following';
        } elseif ($request->health === 'red') {
            $project->tracking_status = 'not_following';
        } elseif ($request->health === 'green') {
            $project->tracking_status = 'completed';
        }

        $project->last_activity_at = Carbon::now();
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
        $project = Project::findOrFail($id);
        $userId = auth()->id() ?? $request->user_id ?? \App\Models\User::first()->id;

        $exists = \Illuminate\Support\Facades\DB::table('pinned_projects')
            ->where('user_id', $userId)
            ->where('project_id', $id)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table('pinned_projects')
                ->where('user_id', $userId)
                ->where('project_id', $id)
                ->delete();
        } else {
            \Illuminate\Support\Facades\DB::table('pinned_projects')->insert([
                'user_id' => $userId,
                'project_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $project = Project::with(['customer', 'lead'])->findOrFail($id);

        return response()->json([
            'message' => 'Cập nhật ghim thành công',
            'project' => $project,
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
}
