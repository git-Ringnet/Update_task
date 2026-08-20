<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->forceFill([
            'api_token' => 'token-' . $user->id,
            'api_token_expires_at' => now()->addDay(),
        ])->save();

        return $user;
    }

    private function project(User $creator, ?User $lead = null): Project
    {
        $customer = Customer::create([
            'name' => 'Khách hàng kiểm thử ' . uniqid(),
            'type' => 'customer',
            'status' => 'green',
            'last_updated_by' => $creator->id,
            'last_activity_at' => now(),
        ]);

        return Project::create([
            'customer_id' => $customer->id,
            'title' => 'Dự án kiểm thử',
            'created_by' => $creator->id,
            'lead_id' => $lead?->id,
            'health' => 'yellow',
            'tracking_status' => 'following',
            'last_activity_at' => now(),
        ]);
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->api_token];
    }

    public function test_only_creator_lead_member_or_admin_can_view_a_project(): void
    {
        $creator = $this->user();
        $lead = $this->user();
        $member = $this->user();
        $outsider = $this->user();
        $admin = $this->user(['is_admin' => true]);
        $project = $this->project($creator, $lead);
        $project->members()->attach($member->id);

        $this->getJson('/api/projects', $this->headers($creator))
            ->assertOk()
            ->assertJsonPath('projects.0.creator_id', $creator->id)
            ->assertJsonPath('projects.0.can_manage_members', true)
            ->assertJsonPath('projects.0.members.0.id', $member->id);

        foreach ([$creator, $lead, $member, $admin] as $allowedUser) {
            $this->getJson("/api/projects/{$project->id}", $this->headers($allowedUser))->assertOk();
        }

        $this->getJson("/api/projects/{$project->id}", $this->headers($outsider))->assertNotFound();
    }

    public function test_removed_member_immediately_loses_project_and_activity_access(): void
    {
        $creator = $this->user();
        $member = $this->user();
        $project = $this->project($creator);
        $project->members()->attach($member->id);

        $this->getJson("/api/projects/{$project->id}/access", $this->headers($member))->assertNoContent();
        $this->putJson("/api/projects/{$project->id}", [
            'member_ids' => [],
        ], $this->headers($creator))
            ->assertOk()
            ->assertJsonCount(0, 'members');

        $this->getJson("/api/projects/{$project->id}/access", $this->headers($member))->assertNotFound();
        $this->getJson("/api/comments?project_id={$project->id}", $this->headers($member))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_tagging_a_user_adds_them_to_project_members(): void
    {
        $creator = $this->user();
        $tagged = $this->user(['name' => 'Nguyễn Văn An', 'email' => 'an@example.test']);
        $project = $this->project($creator);

        $this->postJson('/api/tasks', [
            'project_id' => $project->id,
            'title' => 'Mời @Nguyễn Văn An cập nhật tiến độ',
            'status' => 'todo',
            'priority' => 'medium',
        ], $this->headers($creator))->assertCreated();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $tagged->id,
        ]);
        $this->getJson("/api/projects/{$project->id}", $this->headers($tagged))->assertOk();
    }

    public function test_only_admin_can_create_or_delete_accounts_and_admin_is_hidden(): void
    {
        $member = $this->user();
        $admin = $this->user(['is_admin' => true, 'email' => 'admin@example.test']);
        $payload = [
            'name' => 'Thành viên mới',
            'email' => 'new-member@example.test',
            'password' => 'secret123',
        ];

        $this->postJson('/api/users', $payload, $this->headers($member))->assertForbidden();
        $this->postJson('/api/users', $payload, $this->headers($admin))->assertCreated();

        $this->getJson('/api/users', $this->headers($member))
            ->assertOk()
            ->assertJsonMissing(['email' => $admin->email]);
        $this->getJson('/api/active-users')
            ->assertOk()
            ->assertJsonMissing(['email' => $admin->email]);
    }
}
