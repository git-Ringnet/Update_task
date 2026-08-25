<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_export_database(): void
    {
        $this->getJson('/api/database/export')
            ->assertStatus(401);
    }

    public function test_regular_user_cannot_export_database(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_system_admin' => false,
        ]);
        $user->forceFill([
            'api_token' => 'regular-token',
            'api_token_expires_at' => now()->addDay(),
        ])->save();

        $this->getJson('/api/database/export', [
            'Authorization' => 'Bearer regular-token'
        ])->assertStatus(403);
    }

    public function test_normal_admin_cannot_export_database(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_system_admin' => false,
        ]);
        $admin->forceFill([
            'api_token' => 'admin-token',
            'api_token_expires_at' => now()->addDay(),
        ])->save();

        $this->getJson('/api/database/export', [
            'Authorization' => 'Bearer admin-token'
        ])->assertStatus(403);
    }

    public function test_system_admin_can_export_database(): void
    {
        $systemAdmin = User::factory()->create([
            'is_admin' => true,
            'is_system_admin' => true,
        ]);
        $systemAdmin->forceFill([
            'api_token' => 'sysadmin-token',
            'api_token_expires_at' => now()->addDay(),
        ])->save();

        $response = $this->getJson('/api/database/export', [
            'Authorization' => 'Bearer sysadmin-token'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
        $this->assertStringContainsString('attachment; filename="backup_sqlite_', $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('-- Database SQLite Export', $content);
        $this->assertStringContainsString('PRAGMA foreign_keys = OFF;', $content);
    }
}
