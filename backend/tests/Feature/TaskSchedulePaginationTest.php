<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class TaskSchedulePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bidirectional_schedule_pagination()
    {
        $user = User::factory()->create(['is_admin' => 0, 'is_system_admin' => 1]);
        $user->forceFill([
            'api_token' => 'token-' . $user->id,
            'api_token_expires_at' => now()->addDay(),
        ])->save();
        $customer = Customer::create(['name' => 'Acme Corp']);
        $project = Project::create([
            'title' => 'Project Alpha',
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'tracking_status' => 'planning',
            'health' => 'green',
        ]);

        $today = Carbon::today();

        // 3 past tasks
        $past1 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Past task 1',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->subDays(5)->toDateTimeString(),
        ]);
        $past2 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Past task 2',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->subDays(2)->toDateTimeString(),
        ]);
        $past3 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Past task 3',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->subDays(1)->toDateTimeString(),
        ]);

        // 1 today task
        $todayTask = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Today task',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => $today->copy()->addHours(10)->toDateTimeString(),
        ]);

        // 3 future tasks
        $future1 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Future task 1',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->addDays(1)->toDateTimeString(),
        ]);
        $future2 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Future task 2',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->addDays(3)->toDateTimeString(),
        ]);
        $future3 = Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'Future task 3',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => $today->copy()->addDays(5)->toDateTimeString(),
        ]);

        $headers = [
            'Authorization' => 'Bearer token-' . $user->id,
            'Accept' => 'application/json',
        ];

        // Test 1: Initial load with limit=2 (so 2 past, 1 today, 2 future, has_more_past=true, has_more_future=true)
        $resInitial = $this->withHeaders($headers)->getJson('/api/tasks?view_mode=schedule&paginate=1&limit=2');
        $resInitial->assertOk();
        $dataInitial = $resInitial->json();

        $this->assertTrue($dataInitial['has_more_past']);
        $this->assertTrue($dataInitial['has_more_future']);
        $this->assertCount(5, $dataInitial['tasks']); // 2 past + 1 today + 2 future

        // Check order is chronological ascending
        $titles = array_column($dataInitial['tasks'], 'title');
        $this->assertEquals(['Past task 2', 'Past task 3', 'Today task', 'Future task 1', 'Future task 2'], $titles);

        // Test 2: Load older past tasks (direction=past) before Past task 2
        $resPast = $this->withHeaders($headers)->getJson('/api/tasks?view_mode=schedule&paginate=1&direction=past&before_date=' . $past2->due_date . '&before_id=' . $past2->id . '&limit=2');
        $resPast->assertOk();
        $dataPast = $resPast->json();
        $this->assertFalse($dataPast['has_more_past']);
        $this->assertCount(1, $dataPast['tasks']);
        $this->assertEquals('Past task 1', $dataPast['tasks'][0]['title']);

        // Test 3: Load further future tasks (direction=future) after Future task 2
        $resFuture = $this->withHeaders($headers)->getJson('/api/tasks?view_mode=schedule&paginate=1&direction=future&after_date=' . $future2->due_date . '&after_id=' . $future2->id . '&limit=2');
        $resFuture->assertOk();
        $dataFuture = $resFuture->json();
        $this->assertFalse($dataFuture['has_more_future']);
        $this->assertCount(1, $dataFuture['tasks']);
        $this->assertEquals('Future task 3', $dataFuture['tasks'][0]['title']);
    }
}
