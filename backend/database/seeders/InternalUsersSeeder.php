<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InternalUsersSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Ân',
                'email' => 'an@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Thiên',
                'email' => 'thien@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Tín',
                'email' => 'tin@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Khanh',
                'email' => 'khanh@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Hiếu',
                'email' => 'hieu@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Cảnh',
                'email' => 'canh@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Thắng',
                'email' => 'thang@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&q=80&w=120',
            ],
            [
                'name' => 'Thảo',
                'email' => 'thao@xuongrong.vn',
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=120',
            ],
        ];

        foreach ($members as $m) {
            User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('Ringnet@123'),
                    'avatar' => $m['avatar'],
                    'api_token' => Str::random(60)
                ]
            );
        }
        
        echo "✅ Seeded 8 internal team members successfully!\n";
    }
}
