<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@xuongrong.vn');
        $password = env('ADMIN_PASSWORD', 'password');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Quản trị hệ thống'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );

        DB::table('project_members')->where('user_id', $admin->id)->delete();
    }
}
