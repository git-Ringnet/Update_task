<?php

namespace App\Console\Commands;

use App\Services\SystemBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreSystemBackup extends Command
{
    protected $signature = 'backup:restore {archive : Path to the system backup ZIP file}';
    protected $description = 'Restore database and public files from a system backup ZIP file.';

    public function handle(SystemBackupService $backups): int
    {
        $archivePath = $this->argument('archive');

        if (!File::exists($archivePath)) {
            $this->error("File không tồn tại: {$archivePath}");
            return self::FAILURE;
        }

        $this->info("Bắt đầu khôi phục từ: {$archivePath} ...");

        try {
            $backups->restore($archivePath);
            $this->info("Đã khôi phục thành công toàn bộ database và file hệ thống!");
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error("Khôi phục thất bại: " . $error->getMessage());
            return self::FAILURE;
        }
    }
}
