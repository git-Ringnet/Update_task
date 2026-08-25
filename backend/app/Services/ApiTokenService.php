<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ApiTokenService
{
    public function issue(User $user): string
    {
        $token = Str::random(80);

        $user->apiTokens()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
            'last_used_at' => now(),
        ]);

        return $token;
    }
}
