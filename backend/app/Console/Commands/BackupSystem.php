<?php

namespace App\Console\Commands;

use App\Services\SystemBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class BackupSystem extends Command
{
    protected $signature = 'backup:system {--upload : Upload the completed archive with rclone}';
    protected $description = 'Create a full database and public-files system backup.';

    public function handle(SystemBackupService $backups): int
    {
        $archive = $backups->create();
        $this->info('Created: ' . $archive);
        $backups->pruneLocal();

        if (!$this->option('upload')) return self::SUCCESS;
        $remote = config('backup.rclone_remote');
        if (!$remote) {
            $this->error('RCLONE_REMOTE is not configured.');
            return self::FAILURE;
        }
        $result = Process::timeout(config('backup.rclone_timeout'))
            ->run([config('backup.rclone_binary'), 'copy', $archive, $remote]);
        if ($result->failed()) {
            $this->error($result->errorOutput());
            return self::FAILURE;
        }
        $prune = Process::timeout(config('backup.rclone_timeout'))
            ->run([config('backup.rclone_binary'), 'delete', $remote, '--include', 'system_backup_*.zip', '--min-age', config('backup.retention_days') . 'd']);
        if ($prune->failed()) {
            $this->warn('Upload succeeded but remote retention cleanup failed: ' . $prune->errorOutput());
        }
        $this->info('Uploaded and retention policy applied.');
        return self::SUCCESS;
    }
}
