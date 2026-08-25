<?php

namespace App;

use App\Models\MentionGroup;
use App\Models\Project;
use App\Models\User;

class ProjectMemberService
{
    public function addMentionedMembers(Project $project, ?string $text, array $explicitUserIds = []): void
    {
        $memberIds = collect($explicitUserIds)->map(fn ($id) => (int) $id)->filter();

        if ($text && preg_match('/@all(?=\s|$|[,.;:!?()])/iu', $text)) {
            $memberIds->push(...User::where('is_admin', false)->pluck('id')->all());
        }

        if ($text && str_contains($text, '@')) {
            $groups = MentionGroup::with('members:id')->get();
            foreach ($groups as $group) {
                $pattern = '/@' . preg_quote($group->name, '/') . '(?=\s|$|[,.;:!?()])/iu';
                if (preg_match($pattern, $text)) {
                    $memberIds->push(...$group->members->pluck('id')->all());
                }
            }

            $users = User::where('is_admin', false)->get(['id', 'name', 'email']);
            foreach ($users as $user) {
                $identifiers = array_filter([
                    trim((string) $user->name),
                    strstr((string) $user->email, '@', true),
                ]);

                foreach ($identifiers as $identifier) {
                    $pattern = '/@' . preg_quote($identifier, '/') . '(?=\s|$|[,.;:!?()])/iu';
                    if (preg_match($pattern, $text)) {
                        $memberIds->push($user->id);
                        break;
                    }
                }
            }
        }

        $validIds = User::where('is_admin', false)
            ->whereIn('id', $memberIds->unique()->values())
            ->pluck('id')
            ->all();

        if ($validIds) {
            $project->members()->syncWithoutDetaching($validIds);
        }
    }
}
