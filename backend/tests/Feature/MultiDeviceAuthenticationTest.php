<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiDeviceAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_keep_multiple_device_sessions_and_log_out_of_only_one(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $firstToken = $this->postJson('/api/login', [
            'username' => $user->email,
            'password' => 'secret123',
        ])->assertOk()->json('token');

        $secondToken = $this->postJson('/api/login', [
            'username' => $user->email,
            'password' => 'secret123',
        ])->assertOk()->json('token');

        $this->assertNotSame($firstToken, $secondToken);
        $this->assertDatabaseCount('api_tokens', 2);
        $this->getJson('/api/me', ['Authorization' => "Bearer {$firstToken}"])->assertOk();
        $this->getJson('/api/me', ['Authorization' => "Bearer {$secondToken}"])->assertOk();

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$firstToken}"])->assertOk();

        $this->getJson('/api/me', ['Authorization' => "Bearer {$firstToken}"])->assertUnauthorized();
        $this->getJson('/api/me', ['Authorization' => "Bearer {$secondToken}"])->assertOk();
        $this->assertDatabaseCount('api_tokens', 1);
        $this->assertDatabaseMissing('api_tokens', ['token_hash' => hash('sha256', $firstToken)]);
        $this->assertDatabaseHas('api_tokens', ['token_hash' => hash('sha256', $secondToken)]);
    }

    public function test_an_expired_device_session_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = 'expired-device-token';
        ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subSecond(),
        ]);

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
        $this->assertDatabaseCount('api_tokens', 0);
    }
}
