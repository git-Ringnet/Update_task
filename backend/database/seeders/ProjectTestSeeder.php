<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class ProjectTestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        
        // Sử dụng các users hiện có trong hệ thống (Tài khoản nội bộ)
        $users = User::all();
        if ($users->isEmpty()) {
            $this->call(InternalUsersSeeder::class);
            $users = User::all();
        }

        // Tạo một số customers nếu chưa có
        $customers = Customer::all();
        if ($customers->count() < 20) {
            $companyTypes = ['Technology', 'Finance', 'Healthcare', 'Education', 'Retail', 'Manufacturing'];
            for ($i = $customers->count(); $i < 20; $i++) {
                Customer::create([
                    'name' => $faker->company . ' ' . $faker->randomElement($companyTypes),
                    'email' => $faker->unique()->companyEmail,
                    'phone' => $faker->phoneNumber,
                ]);
            }
            $customers = Customer::all();
        }

        // Danh sách tên dự án mẫu
        $projectTypes = [
            'Website Development',
            'Mobile App',
            'E-commerce Platform',
            'CRM System',
            'ERP Implementation',
            'Digital Marketing',
            'SEO Optimization',
            'Cloud Migration',
            'Database Optimization',
            'API Integration',
            'UI/UX Redesign',
            'Security Audit',
            'Performance Testing',
            'Data Analytics',
            'Business Intelligence',
        ];

        $projectPrefixes = [
            'Dự án',
            'Hệ thống',
            'Ứng dụng',
            'Website',
            'Platform',
            'Giải pháp',
            'Nền tảng',
        ];

        $healthStatuses = ['green', 'yellow', 'red'];
        
        // Tạo 100 dự án
        echo "Creating 100 test projects...\n";
        
        for ($i = 1; $i <= 100; $i++) {
            $customer = $customers->random();
            $lead = $users->random();
            $health = $faker->randomElement($healthStatuses);
            
            // Tạo thời gian hoạt động ngẫu nhiên trong 90 ngày qua
            $daysAgo = rand(0, 90);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);
            $lastActivity = Carbon::now()->subDays($daysAgo)->subHours($hoursAgo)->subMinutes($minutesAgo);

            $project = Project::create([
                'customer_id' => $customer->id,
                'title' => $faker->randomElement($projectPrefixes) . ' ' . 
                          $faker->randomElement($projectTypes) . ' ' . 
                          $customer->name . ' #' . $i,
                'lead_id' => $lead->id,
                'health' => $health,
                'tracking_status' => $this->getTrackingStatus($health),
                'sort_order' => $i,
                'last_activity_at' => $lastActivity,
                'created_at' => $lastActivity->copy()->subDays(rand(1, 30)),
                'updated_at' => $lastActivity,
            ]);

            // Tạo comment khởi tạo
            Comment::create([
                'project_id' => $project->id,
                'user_id' => $lead->id,
                'content' => "Dự án mới",
                'type' => 'status_change',
                'created_at' => $project->created_at,
                'updated_at' => $project->created_at,
            ]);

            // Tạo 1-3 comments ngẫu nhiên cho mỗi dự án
            $commentCount = rand(1, 3);
            for ($j = 0; $j < $commentCount; $j++) {
                $commentUser = $users->random();
                $commentDate = $lastActivity->copy()->subDays(rand(0, 7));
                
                $commentTexts = [
                    "Đã cập nhật tiến độ dự án. Mọi thứ đang diễn ra tốt đẹp.",
                    "Họp với khách hàng thành công. Đã thống nhất yêu cầu mới.",
                    "Hoàn thành giai đoạn 1. Chuyển sang giai đoạn 2.",
                    "Đã fix xong các bug được báo cáo.",
                    "Triển khai lên môi trường staging để test.",
                    "Khách hàng yêu cầu điều chỉnh một số tính năng.",
                    "Team đang làm việc chăm chỉ để hoàn thành đúng deadline.",
                    "Cần thêm nguồn lực cho module thanh toán.",
                ];

                Comment::create([
                    'project_id' => $project->id,
                    'user_id' => $commentUser->id,
                    'content' => $faker->randomElement($commentTexts),
                    'type' => 'comment',
                    'created_at' => $commentDate,
                    'updated_at' => $commentDate,
                ]);
            }

            if ($i % 10 == 0) {
                echo "Created {$i} projects...\n";
            }
        }

        echo "✅ Successfully created 100 test projects!\n";
    }

    private function getTrackingStatus($health)
    {
        return match($health) {
            'green' => 'completed',
            'yellow' => 'following',
            'red' => 'not_following',
            default => 'following',
        };
    }
}
