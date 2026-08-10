<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Task;

class CleanFakeUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure internal team members exist
        $this->call(InternalUsersSeeder::class);

        $fallbackUser = User::where('email', 'LIKE', '%@xuongrong.vn%')->first() 
                        ?? User::where('email', 'NOT LIKE', '%@example.com%')->first();

        if (!$fallbackUser) {
            echo "No valid internal fallback user found.\n";
            return;
        }

        // 2. Find all fake @example.com users
        $fakeUsers = User::where('email', 'LIKE', '%@example.com%')->get();
        $fakeUserIds = $fakeUsers->pluck('id')->toArray();

        if (empty($fakeUserIds)) {
            echo "No fake @example.com users found in database.\n";
            return;
        }

        // 3. Reassign projects, comments, tasks from fake users to fallback user
        Project::whereIn('lead_id', $fakeUserIds)->update(['lead_id' => $fallbackUser->id]);
        Comment::whereIn('user_id', $fakeUserIds)->update(['user_id' => $fallbackUser->id]);
        Task::whereIn('assignee_id', $fakeUserIds)->update(['assignee_id' => $fallbackUser->id]);
        Task::whereIn('created_by', $fakeUserIds)->update(['created_by' => $fallbackUser->id]);

        // 4. Delete the fake users
        $deletedCount = User::whereIn('id', $fakeUserIds)->delete();

        echo "✅ Successfully deleted {$deletedCount} fake users (@example.com) and reassigned their records!\n";
    }
}
