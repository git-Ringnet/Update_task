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
        $this->info('🚀 Bắt đầu đồng bộ các hoạt động từ chi tiết dự án sang hoạt động của tôi/đội...');

        $memberService = app(ProjectMemberService::class);
        $tasks = Task::with(['project', 'attachments'])->orderBy('created_at', 'asc')->get();
        $this->info("Tổng số công việc/cập nhật được tìm thấy: {$tasks->count()}");

        $createdCount = 0;
        $linkedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $privacySyncedCount = 0;

        foreach ($tasks as $task) {
            $project = $task->project;
            if (!$project) {
                $this->warn("⚠️ Task #{$task->id} không thuộc dự án nào. Bỏ qua.");
                continue;
            }

            // 1. Phân tích quyền riêng tư từ tiêu đề task và danh sách private_user_ids
            $extractedPrivateIds = $memberService->extractPrivateMentionUserIds($task->title, $task->private_user_ids ?? []);
            $isPrivate = !empty($extractedPrivateIds) || (bool) $task->is_private;
            $finalPrivateUserIds = !empty($extractedPrivateIds) 
                ? array_values(array_unique($extractedPrivateIds)) 
                : ($task->private_user_ids ? array_values(array_unique($task->private_user_ids)) : null);

            // Cập nhật lại privacy trên task nếu chưa khớp
            if ((bool) $task->is_private !== $isPrivate || json_encode($task->private_user_ids) !== json_encode($finalPrivateUserIds)) {
                Task::withoutEvents(function () use ($task, $isPrivate, $finalPrivateUserIds) {
                    $task->update([
                        'is_private' => $isPrivate,
                        'private_user_ids' => $finalPrivateUserIds,
                    ]);
                });
                $privacySyncedCount++;
            }

            // 2. Kiểm tra xem comment đã liên kết trực tiếp qua task_id chưa
            $existingComment = Comment::where('task_id', $task->id)->first();

            // 3. Nếu chưa có, tìm comment có cùng project_id, user_id và nội dung
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

            // 4. Nếu đã có comment (hoặc vừa liên kết), đảm bảo nội dung, privacy và health đồng bộ
            if ($existingComment) {
                $needsUpdate = false;
                $updateData = [];

                if ($existingComment->content !== $task->title) {
                    $updateData['content'] = $task->title;
                    $needsUpdate = true;
                }
                if ((bool) $existingComment->is_private !== $isPrivate) {
                    $updateData['is_private'] = $isPrivate;
                    $needsUpdate = true;
                }
                if (json_encode($existingComment->private_user_ids) !== json_encode($finalPrivateUserIds)) {
                    $updateData['private_user_ids'] = $finalPrivateUserIds;
                    $needsUpdate = true;
                }
                $expectedHealth = $task->health ?? $project->health ?? 'green';
                if ($existingComment->project_health !== $expectedHealth) {
                    $updateData['project_health'] = $expectedHealth;
                    $needsUpdate = true;
                }
                if ($existingComment->task_id !== $task->id) {
                    $updateData['task_id'] = $task->id;
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    Comment::withoutEvents(function () use ($existingComment, $updateData) {
                        $existingComment->update($updateData);
                    });
                    $updatedCount++;
                    $this->line("  🔄 Đã cập nhật Comment #{$existingComment->id} đồng bộ với Task #{$task->id}");
                } else {
                    $skippedCount++;
                }
            } else {
                // 5. Nếu vẫn chưa có comment nào cho task này, tạo mới Comment để hiển thị ở Hoạt động của tôi/đội
                $userId = $task->created_by 
                    ?? $project->lead_id 
                    ?? $project->created_by 
                    ?? \App\Models\User::where('is_admin', false)->first()?->id 
                    ?? 1;

                Comment::withoutEvents(function () use ($task, $project, $userId, $isPrivate, $finalPrivateUserIds) {
                    Comment::create([
                        'project_id' => $task->project_id,
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'content' => $task->title,
                        'type' => 'comment',
                        'is_private' => $isPrivate,
                        'private_user_ids' => $finalPrivateUserIds,
                        'project_health' => $task->health ?? $project->health ?? 'green',
                        'created_at' => $task->created_at ?? Carbon::now(),
                        'updated_at' => $task->updated_at ?? Carbon::now(),
                    ]);
                });

                $createdCount++;
                $this->line("  ➕ Đã tạo hoạt động mới cho Task #{$task->id} (Dự án: {$project->title})");
            }

            // Đảm bảo các thành viên được tag hoặc gửi riêng được thêm vào project
            if ($task->title) {
                $explicitIds = array_filter(array_merge($finalPrivateUserIds ?? [], [$task->assignee_id]));
                $memberService->addMentionedMembers($project, $task->title, $explicitIds);
            }
        }

        // 6. Quét các standalone comments (không có task_id) để đảm bảo đồng bộ quyền riêng tư
        $standaloneComments = Comment::whereNull('task_id')->with('project')->get();
        foreach ($standaloneComments as $comment) {
            $extractedPrivateIds = $memberService->extractPrivateMentionUserIds($comment->content, $comment->private_user_ids ?? []);
            if (!empty($extractedPrivateIds)) {
                $privIds = array_values(array_unique($extractedPrivateIds));
                if (!$comment->is_private || json_encode($comment->private_user_ids) !== json_encode($privIds)) {
                    Comment::withoutEvents(function () use ($comment, $privIds) {
                        $comment->update([
                            'is_private' => true,
                            'private_user_ids' => $privIds,
                        ]);
                    });
                    $privacySyncedCount++;
                }
            }
            if ($comment->project && $comment->content) {
                $memberService->addMentionedMembers($comment->project, $comment->content, $comment->private_user_ids ?? []);
            }
        }

        // 7. Cập nhật lại last_activity_at cho các dự án dựa trên hoạt động/comment mới nhất
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
                ['Đã tạo mới (thiếu trong hoạt động)', $createdCount],
                ['Đã cập nhật nội dung/trạng thái khớp với chi tiết', $updatedCount],
                ['Đã liên kết với comment cũ', $linkedCount],
                ['Đã đồng bộ lại quyền riêng tư (is_private)', $privacySyncedCount],
                ['Đã khớp sẵn (không cần đổi)', $skippedCount],
                ['Tổng số task đã xử lý', $tasks->count()],
            ]
        );

        return Command::SUCCESS;
    }
}

