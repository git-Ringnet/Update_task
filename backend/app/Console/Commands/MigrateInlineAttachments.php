<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateInlineAttachments extends Command
{
    protected $signature = 'attachments:migrate-inline {--force : Apply changes (otherwise only report)}';

    protected $description = 'Move legacy base64 attachments from task titles and comments to public storage.';

    private array $urlsByHash = [];
    private int $migrated = 0;

    public function handle(): int
    {
        $taskCount = Task::where('title', 'like', '%data:%base64%')->count();
        $commentCount = Comment::where('content', 'like', '%data:%base64%')->count();

        $this->info("Found {$taskCount} task(s) and {$commentCount} comment(s) with inline base64 data.");
        if (!$this->option('force')) {
            $this->warn('Dry run only. Run again with --force to migrate these files.');
            return self::SUCCESS;
        }

        // One row at a time prevents several large legacy blobs being held in memory together.
        Task::where('title', 'like', '%data:%base64%')->orderBy('id')->chunkById(1, function ($tasks) {
            foreach ($tasks as $task) {
                $title = $this->replaceDataUrls($task->title, $task->id);
                if ($title !== $task->title) {
                    $task->forceFill(['title' => $title])->save();
                }
            }
        });

        Comment::where('content', 'like', '%data:%base64%')->orderBy('id')->chunkById(1, function ($comments) {
            foreach ($comments as $comment) {
                $content = $this->replaceDataUrls($comment->content, $comment->task_id);
                if ($content !== $comment->content) {
                    $comment->forceFill(['content' => $content])->save();
                }
            }
        });

        $this->info("Migrated {$this->migrated} unique inline file(s) to storage/app/public/attachments.");
        return self::SUCCESS;
    }

    private function replaceDataUrls(string $html, ?int $taskId): string
    {
        $html = preg_replace_callback(
            '/(!?\[[^\]]*\]\()(data:([^;,]+);base64,([^)]+))(\))/i',
            fn (array $match): string => $match[1] . $this->migrateDataUrl($match[2], $match[3], $match[4], $taskId) . $match[5],
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/((?:src|href)=["\'])(data:([^;,]+);base64,([^"\']+))(["\'])/i',
            fn (array $match): string => $match[1] . $this->migrateDataUrl($match[2], $match[3], $match[4], $taskId) . $match[5],
            $html
        ) ?? $html;
    }

    private function migrateDataUrl(string $dataUrl, string $mimeType, string $encoded, ?int $taskId): string
    {
        $mimeType = strtolower($mimeType);
        $hash = hash('sha256', $dataUrl);

        if (!isset($this->urlsByHash[$hash])) {
            $contents = base64_decode($encoded, true);
            if ($contents === false) {
                return $dataUrl;
            }

            $extension = $this->extensionForMime($mimeType);
            $path = 'attachments/legacy_' . Str::random(16) . '.' . $extension;
            Storage::disk('public')->put($path, $contents);

            $attachment = Attachment::create([
                'task_id' => $taskId,
                'original_name' => 'legacy_attachment.' . $extension,
                'file_path' => $path,
                'mime_type' => $mimeType,
                'size' => strlen($contents),
            ]);

            $this->urlsByHash[$hash] = Storage::url($attachment->file_path);
            $this->migrated++;
        }

        return $this->urlsByHash[$hash];
    }

    private function extensionForMime(string $mimeType): string
    {
        return match ($mimeType) {
            'application/pdf' => 'pdf',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/zip' => 'zip',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'text/plain' => 'txt',
            default => 'bin',
        };
    }
}
