<?php

namespace App;

use App\Models\MentionGroup;
use App\Models\Project;
use App\Models\User;

class ProjectMemberService
{
    /**
     * Extract user IDs mentioned privately with prefix '"' (e.g. "Hiếu, "Hiền).
     *
     * @param string|null $text
     * @param array $explicitUserIds
     * @return array
     */
    public function extractPrivateMentionUserIds(?string $text, array $explicitUserIds = []): array
    {
        $memberIds = collect($explicitUserIds)->map(fn ($id) => (int) $id)->filter();

        $cleanText = $text ? preg_replace('/^\[reply:\{.*?\}\]\s*/', '', $text) : '';

        if ($cleanText && (str_contains($cleanText, '"') || str_contains($cleanText, '“') || str_contains($cleanText, '”'))) {
            $users = User::where('is_admin', false)->get(['id', 'name', 'email']);
            // Sort by longest name first to prevent partial prefix match
            $users = $users->sortByDesc(fn ($u) => mb_strlen((string) $u->name));

            foreach ($users as $user) {
                $name = trim((string) $user->name);
                $emailPrefix = strstr((string) $user->email, '@', true) ?: '';
                $identifiers = array_filter([$name, $emailPrefix]);

                foreach ($identifiers as $identifier) {
                    $pattern = '/(?<!\w)["“”]' . preg_quote($identifier, '/') . '(?=\s|$|[,.;:!?()"\'“”<])/iu';
                    if (preg_match($pattern, $cleanText)) {
                        $memberIds->push($user->id);
                        break;
                    }
                }
            }
        }

        return User::where('is_admin', false)
            ->whereIn('id', $memberIds->unique()->values())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function addMentionedMembers(Project $project, ?string $text, array $explicitUserIds = []): void
    {
        if ($explicitUserIds === [] && (!$text || (!str_contains($text, '@') && !str_contains($text, '"')))) {
            return;
        }

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

        if ($text && (str_contains($text, '"') || str_contains($text, '“') || str_contains($text, '”'))) {
            $privateIds = $this->extractPrivateMentionUserIds($text);
            $memberIds->push(...$privateIds);
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

