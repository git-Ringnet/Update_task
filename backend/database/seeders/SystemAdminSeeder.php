<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SYSTEM_ADMIN_PASSWORD');

        if (!$password) {
            throw new \RuntimeException('SYSTEM_ADMIN_PASSWORD must be set before creating the System Admin account.');
        }

        User::updateOrCreate(
            ['email' => env('SYSTEM_ADMIN_EMAIL', 'system-admin@xuongrong.vn')],
            [
                'name' => env('SYSTEM_ADMIN_NAME', 'System Admin'),
                'password' => Hash::make($password),
                'is_admin' => true,
                'is_system_admin' => true,
            ]
        );
    }
}
