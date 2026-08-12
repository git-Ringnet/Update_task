<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MilestoneController extends Controller
{
    public function index($projectId)
    {
        $milestones = Milestone::where('project_id', $projectId)
            ->with('creator')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($milestones);
    }

    public function store(Request $request, $projectId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_completed' => 'boolean',
        ]);

        $project = Project::findOrFail($projectId);

        $validated['project_id'] = $project->id;
        $validated['is_completed'] = $request->has('is_completed') ? (bool)$request->is_completed : false;
        $validated['created_by'] = auth()->id() ?? $request->user_id ?? $project->lead_id ?? \App\Models\User::first()->id ?? null;

        $milestone = Milestone::create($validated);
        $milestone->load('creator');

        return response()->json($milestone, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_completed' => 'sometimes|boolean',
        ]);

        $milestone = Milestone::findOrFail($id);
        $milestone->update($validated);
        $milestone->load('creator');

        return response()->json($milestone);
    }

    public function destroy($id)
    {
        $milestone = Milestone::findOrFail($id);
        $milestone->delete();

        return response()->json(['message' => 'Đã xóa cột mốc']);
    }
}
