<?php

namespace App\Http\Controllers;

use App\Models\MentionGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentionGroupController extends Controller
{
    public function index()
    {
        return response()->json(MentionGroup::with('members:id,name,email,avatar')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $group = MentionGroup::create(['name' => $data['name'], 'created_by' => auth()->id()]);
        $group->members()->sync($data['member_ids'] ?? []);

        return response()->json($group->load('members:id,name,email,avatar'), 201);
    }

    public function update(Request $request, MentionGroup $mentionGroup)
    {
        $data = $this->validated($request, $mentionGroup->id);
        $mentionGroup->update(['name' => $data['name']]);
        $mentionGroup->members()->sync($data['member_ids'] ?? []);

        return response()->json($mentionGroup->load('members:id,name,email,avatar'));
    }

    public function destroy(MentionGroup $mentionGroup)
    {
        $mentionGroup->delete();
        return response()->noContent();
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('mention_groups', 'name')->ignore($ignoreId)],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => Rule::exists('users', 'id')->where('is_admin', false),
        ]);
    }
}
