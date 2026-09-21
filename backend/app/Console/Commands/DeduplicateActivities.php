<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('project:deduplicate-activities {--dry-run : Chỉ quét và hiển thị các bản ghi trùng lặp mà không xóa}')]
#[Description('Quét và dọn dẹp các hoạt động/bình luận bị trùng lặp trong cơ sở dữ liệu')]
class DeduplicateActivities extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 Đang chạy ở chế độ DRY-RUN (chỉ kiểm tra, không xóa dữ liệu)...');
        } else {
            $this->info('🚀 Bắt đầu quét và dọn dẹp các bình luận/hoạt động trùng lặp...');
        }

        $duplicateTaskIdCount = 0;
        $duplicateTaskCommentCount = 0;
        $duplicateIdenticalCommentCount = 0;
        $deletedIds = [];

        // 1. Dọn dẹp các comment có CÙNG task_id (chỉ giữ lại 1 comment đầu tiên)
        $tasksWithMultipleComments = Comment::whereNotNull('task_id')
            ->select('task_id')
            ->groupBy('task_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('task_id');

        foreach ($tasksWithMultipleComments as $taskId) {
            $comments = Comment::where('task_id', $taskId)->orderBy('id', 'asc')->get();
            // Giữ lại cái đầu tiên, xóa các cái sau
            $first = $comments->shift();
            foreach ($comments as $dup) {
                $deletedIds[] = $dup->id;
                $duplicateTaskIdCount++;
                $this->line("  🗑️ [Cùng Task ID #{$taskId}] Trùng lặp Comment #{$dup->id} (giữ lại #{$first->id})");
                if (!$isDryRun) {
                    Comment::withoutEvents(fn() => $dup->delete());
                }
            }
        }

        // 2. Dọn dẹp comment không có task_id nhưng trùng nội dung, project, user với 1 task đã có comment
        $tasks = Task::all();
        foreach ($tasks as $task) {
            $taskComments = Comment::where('task_id', $task->id)->get();
            if ($taskComments->isNotEmpty()) {
                // Tìm comment không có task_id cùng project, user, content
                $unlinkedDuplicates = Comment::where('project_id', $task->project_id)
                    ->where('user_id', $task->created_by)
                    ->where('content', $task->title)
                    ->whereNull('task_id')
                    ->whereNotIn('id', $deletedIds)
                    ->get();

                foreach ($unlinkedDuplicates as $dup) {
                    // Kiểm tra thời gian tạo gần nhau (trong vòng 10 phút)
                    $taskTime = $task->created_at ? Carbon::parse($task->created_at)->timestamp : 0;
                    $dupTime = $dup->created_at ? Carbon::parse($dup->created_at)->timestamp : 0;

                    if (abs($taskTime - $dupTime) <= 600) {
                        $deletedIds[] = $dup->id;
                        $duplicateTaskCommentCount++;
                        $this->line("  🗑️ [Trùng với Task #{$task->id}] Xóa Comment thừa #{$dup->id}: \"{$dup->content}\"");
                        if (!$isDryRun) {
                            Comment::withoutEvents(fn() => $dup->delete());
                        }
                    }
                }
            }
        }

        // 3. Dọn dẹp các comment giống hệt nhau được tạo liên tiếp (cùng project, user, content, type trong vòng 60 giây)
        $allComments = Comment::whereNotIn('id', $deletedIds)
            ->orderBy('project_id')
            ->orderBy('user_id')
            ->orderBy('id', 'asc')
            ->get();

        $grouped = $allComments->groupBy(fn($c) => "{$c->project_id}_{$c->user_id}_{$c->type}");

        foreach ($grouped as $key => $items) {
            if ($items->count() < 2) continue;

            $itemsArray = $items->values()->all();
            for ($i = 0; $i < count($itemsArray); $i++) {
                $current = $itemsArray[$i];
                if (in_array($current->id, $deletedIds)) continue;

                for ($j = $i + 1; $j < count($itemsArray); $j++) {
                    $next = $itemsArray[$j];
                    if (in_array($next->id, $deletedIds)) continue;

                    // Nếu cùng content và thời gian tạo cách nhau <= 60s
                    if (trim($current->content) === trim($next->content)) {
                        $timeDiff = abs(Carbon::parse($current->created_at)->diffInSeconds(Carbon::parse($next->created_at)));
                        if ($timeDiff <= 60) {
                            $deletedIds[] = $next->id;
                            $duplicateIdenticalCommentCount++;
                            $this->line("  🗑️ [Comment trùng lặp] Xóa Comment #{$next->id} (trùng với #{$current->id}: \"{$next->content}\")");
                            if (!$isDryRun) {
                                Comment::withoutEvents(fn() => $next->delete());
                            }
                        }
                    }
                }
            }
        }

        $totalCleaned = count($deletedIds);
        $this->newLine();
        if ($isDryRun) {
            $this->info("✅ Hoàn tất kiểm tra (DRY-RUN)! Tìm thấy {$totalCleaned} bản ghi trùng lặp.");
        } else {
            $this->info("✅ Đã dọn dẹp xong {$totalCleaned} bản ghi trùng lặp!");
        }

        $this->table(
            ['Loại trùng lặp', 'Số lượng đã xử lý'],
            [
                ['Trùng lặp cùng Task ID', $duplicateTaskIdCount],
                ['Bình luận thừa trùng với Task đã tạo', $duplicateTaskCommentCount],
                ['Bình luận gửi lặp lại trong 60s', $duplicateIdenticalCommentCount],
                ['Tổng số bản ghi dọn dẹp', $totalCleaned],
            ]
        );

        return Command::SUCCESS;
    }
}
