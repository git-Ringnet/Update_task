<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->forceFill([
            'api_token' => 'token-' . $user->id,
            'api_token_expires_at' => now()->addDay(),
        ])->save();

        return $user;
    }

    public function test_tasks_index_supports_schedule_and_filters(): void
    {
        $user = $this->createUser(['is_admin' => 0]);
        $customer = Customer::create([
            'name' => 'Khách hàng test',
            'type' => 'customer',
            'status' => 'green',
            'last_updated_by' => $user->id,
            'last_activity_at' => now(),
        ]);

        $project = Project::create([
            'customer_id' => $customer->id,
            'title' => 'Dự án test',
            'created_by' => $user->id,
            'health' => 'green',
            'tracking_status' => 'following',
            'last_activity_at' => now(),
        ]);

        $taskScheduled = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Scheduled Task 1',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(2),
        ]);

        $taskDone = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Done Task',
            'status' => 'done',
            'priority' => 'low',
            'due_date' => now()->addDays(1),
        ]);

        $taskNoDueDate = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'No date task',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => null,
        ]);

        $headers = [
            'Authorization' => 'Bearer token-' . $user->id,
            'Accept' => 'application/json',
        ];

        // 1. Basic fetch returns all user's tasks
        $resAll = $this->withHeaders($headers)->getJson('/api/tasks');
        $resAll->assertOk();
        $this->assertCount(3, $resAll->json());

        // 2. Schedule view mode filter
        $resSchedule = $this->withHeaders($headers)->getJson('/api/tasks?view_mode=schedule');
        $resSchedule->assertOk();
        $this->assertCount(1, $resSchedule->json());
        $this->assertEquals($taskScheduled->id, $resSchedule->json()[0]['id']);

        // 3. Exclude done status
        $resExcludeDone = $this->withHeaders($headers)->getJson('/api/tasks?exclude_status=done');
        $resExcludeDone->assertOk();
        $this->assertCount(2, $resExcludeDone->json());

        // 4. Has due date
        $resHasDueDate = $this->withHeaders($headers)->getJson('/api/tasks?has_due_date=1');
        $resHasDueDate->assertOk();
        $this->assertCount(2, $resHasDueDate->json());
    }
}
