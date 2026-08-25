<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAdminProjectPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_can_see_hidden_projects_but_normal_admin_cannot(): void
    {
        $systemAdmin = User::factory()->create(['is_admin' => true, 'is_system_admin' => true]);
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = Customer::create([
            'name' => 'Private customer',
            'type' => 'customer',
            'status' => 'green',
            'last_activity_at' => now(),
        ]);
        $project = Project::create([
            'customer_id' => $customer->id,
            'title' => 'Private project',
            'created_by' => $systemAdmin->id,
            'health' => 'yellow',
            'tracking_status' => 'following',
            'hidden_from_admin' => true,
            'last_activity_at' => now(),
        ]);

        $this->assertTrue($project->isVisibleTo($systemAdmin));
        $this->assertFalse($project->isVisibleTo($admin));
        $this->assertTrue($project->canManageMembers($systemAdmin));
        $this->assertFalse($project->canManageMembers($admin));
    }

    public function test_hidden_projects_are_not_included_in_an_admins_member_project_count(): void
    {
        $systemAdmin = User::factory()->create(['is_admin' => true, 'is_system_admin' => true]);
        $admin = User::factory()->create(['is_admin' => true]);
        $member = User::factory()->create();
        $admin->forceFill([
            'api_token' => 'admin-token',
            'api_token_expires_at' => now()->addDay(),
        ])->save();
        $customer = Customer::create([
            'name' => 'Private customer',
            'type' => 'customer',
            'status' => 'green',
            'last_activity_at' => now(),
        ]);
        $project = Project::create([
            'customer_id' => $customer->id,
            'title' => 'Private project',
            'created_by' => $systemAdmin->id,
            'health' => 'yellow',
            'tracking_status' => 'following',
            'hidden_from_admin' => true,
            'last_activity_at' => now(),
        ]);
        $project->members()->attach($member->id);

        $this->getJson('/api/users', ['Authorization' => 'Bearer admin-token'])
            ->assertOk()
            ->assertJsonPath('0.id', $member->id)
            ->assertJsonPath('0.participating_projects_count', 0);
    }
}
