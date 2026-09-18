<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\ProjectMemberService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('project:sync-activities {--force : Force sync even if task already has a comment}')]
#[Description('Sync missing activities/comments from tasks created inside project details to team activity feed')]
class SyncMissingActivities extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Bắt đầu đồng bộ các hoạt động bị thiếu từ chi tiết dự án sang hoạt động của đội...');

        $tasks = Task::with(['project', 'attachments'])->orderBy('created_at', 'asc')->get();
        $this->info("Tổng số công việc/cập nhật được tìm thấy: {$tasks->count()}");

        $memberService = app(ProjectMemberService::class);
        $createdCount = 0;
        $linkedCount = 0;
        $skippedCount = 0;

        foreach ($tasks as $task) {
            $project = $task->project;
            if (!$project) {
                $this->warn("⚠️ Task #{$task->id} không thuộc dự án nào. Bỏ qua.");
                continue;
            }

            // 1. Kiểm tra xem comment đã liên kết trực tiếp qua task_id chưa
            $existingComment = Comment::where('task_id', $task->id)->first();

            // 2. Nếu chưa có, tìm comment có cùng project_id, user_id và nội dung
            if (!$existingComment) {
                $existingComment = Comment::where('project_id', $task->project_id)
                    ->where('user_id', $task->created_by)
                    ->where('content', $task->title)
                    ->whereNull('task_id')
                    ->first();

                if ($existingComment) {
                    $existingComment->update(['task_id' => $task->id]);
                    $linkedCount++;
                    $this->line("  🔗 Đã liên kết Task #{$task->id} với Comment #{$existingComment->id} sẵn có.");
                }
            }

            // 3. Nếu vẫn chưa có comment nào cho task này, tạo mới Comment để hiển thị ở Hoạt động của đội
            if (!$existingComment) {
                $userId = $task->created_by 
                    ?? $project->lead_id 
                    ?? $project->created_by 
                    ?? \App\Models\User::where('is_admin', false)->first()?->id 
                    ?? 1;

                // Sử dụng withoutEvents để không kích hoạt Web Push thông báo lại các hoạt động lịch sử
                Comment::withoutEvents(function () use ($task, $project, $userId) {
                    Comment::create([
                        'project_id' => $task->project_id,
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'content' => $task->title,
                        'type' => 'comment',
                        'project_health' => $task->health ?? $project->health ?? 'green',
                        'created_at' => $task->created_at ?? Carbon::now(),
                        'updated_at' => $task->updated_at ?? Carbon::now(),
                    ]);
                });

                // Đảm bảo các thành viên được tag hoặc giao việc được thêm vào project
                if ($task->title) {
                    $explicitIds = array_filter([$task->assignee_id]);
                    $memberService->addMentionedMembers($project, $task->title, $explicitIds);
                }

                $createdCount++;
                $this->line("  ➕ Đã tạo hoạt động mới cho Task #{$task->id} (Dự án: {$project->title})");
            } else {
                $skippedCount++;
            }
        }

        // Cập nhật lại last_activity_at cho các dự án dựa trên hoạt động/comment mới nhất
        $projects = Project::all();
        foreach ($projects as $project) {
            $latestCommentTime = Comment::where('project_id', $project->id)->max('created_at');
            $latestTaskTime = Task::where('project_id', $project->id)->max('created_at');
            
            $maxTime = null;
            if ($latestCommentTime && $latestTaskTime) {
                $maxTime = max($latestCommentTime, $latestTaskTime);
            } elseif ($latestCommentTime) {
                $maxTime = $latestCommentTime;
            } elseif ($latestTaskTime) {
                $maxTime = $latestTaskTime;
            }

            if ($maxTime && (!$project->last_activity_at || Carbon::parse($project->last_activity_at)->lt(Carbon::parse($maxTime)))) {
                $project->update(['last_activity_at' => $maxTime]);
            }
        }

        $this->newLine();
        $this->info("✅ Hoàn tất đồng bộ hoạt động!");
        $this->table(
            ['Trạng thái', 'Số lượng'],
            [
                ['Đã tạo mới (thiếu trước đó)', $createdCount],
                ['Đã liên kết với comment cũ', $linkedCount],
                ['Đã tồn tại sẵn', $skippedCount],
                ['Tổng số task đã xử lý', $tasks->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
