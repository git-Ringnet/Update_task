<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class SystemBackupService
{
    public function create(): string
    {
        $this->requireZip();
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);
        $stamp = now()->format('Ymd_His');
        $archive = $backupDir . DIRECTORY_SEPARATOR . "system_backup_{$stamp}.zip";
        $sqlPath = tempnam(sys_get_temp_dir(), 'system_backup_');

        try {
            $this->writeSql($sqlPath);
            $zip = new ZipArchive();
            if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Không thể tạo file ZIP backup.');
            }
            $zip->addFile($sqlPath, 'database.sql');
            $zip->addFromString('manifest.json', json_encode([
                'format' => 'xuong-rong-system-backup',
                'version' => 1,
                'created_at' => now()->toIso8601String(),
                'database_driver' => DB::connection()->getDriverName(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->addDirectory($zip, storage_path('app/public'), 'files');
            $zip->close();
            return $archive;
        } catch (\Throwable $error) {
            File::delete($archive);
            throw $error;
        } finally {
            File::delete($sqlPath);
        }
    }

    public function restore(string $archivePath): void
    {
        $this->requireZip();
        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) throw new RuntimeException('File backup ZIP không hợp lệ.');
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        if (($manifest['format'] ?? null) !== 'xuong-rong-system-backup' || !$zip->getFromName('database.sql')) {
            $zip->close();
            throw new RuntimeException('Đây không phải file backup của hệ thống.');
        }

        $total = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'] ?? '';
            if (!in_array($name, ['database.sql', 'manifest.json'], true) && !str_starts_with($name, 'files/')) {
                $zip->close();
                throw new RuntimeException('File backup chứa đường dẫn không được phép.');
            }
            $total += (int) ($stat['size'] ?? 0);
            if ($total > config('backup.max_import_bytes')) {
                $zip->close();
                throw new RuntimeException('File backup vượt giới hạn khôi phục.');
            }
        }

        $stage = storage_path('app/backup-import/' . bin2hex(random_bytes(12)));
        File::ensureDirectoryExists($stage);
        try {
            $sql = (string) $zip->getFromName('database.sql');
            foreach ($this->zipFiles($zip) as $name) {
                $target = $stage . DIRECTORY_SEPARATOR . substr($name, strlen('files/'));
                File::ensureDirectoryExists(dirname($target));
                File::put($target, $zip->getFromName($name));
            }
            $zip->close();
            $this->runSql($sql);
            $this->replacePublicFiles($stage);
        } finally {
            $zip->close();
            File::deleteDirectory($stage);
        }
    }

    public function pruneLocal(): void
    {
        $cutoff = now()->subDays(config('backup.retention_days'));
        foreach (File::glob(storage_path('app/backups/system_backup_*.zip')) as $path) {
            if (File::lastModified($path) < $cutoff->timestamp) File::delete($path);
        }
    }

    private function writeSql(string $path): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $handle = fopen($path, 'wb');
        if (!$handle) throw new RuntimeException('Không thể tạo file SQL backup.');
        fwrite($handle, "-- System backup generated at " . now()->toDateTimeString() . "\n");
        fwrite($handle, $driver === 'mysql' ? "SET FOREIGN_KEY_CHECKS=0;\n\n" : "PRAGMA foreign_keys = OFF;\n\n");
        $tables = $driver === 'mysql'
            ? array_map(fn ($row) => array_values((array) $row)[0], DB::select('SHOW TABLES'))
            : array_map(fn ($row) => $row->name, DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
        foreach ($tables as $table) {
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            if ($driver === 'mysql') {
                $create = array_values((array) DB::select("SHOW CREATE TABLE {$quoted}")[0])[1];
            } else {
                $create = DB::select('SELECT sql FROM sqlite_master WHERE type=? AND name=?', ['table', $table])[0]->sql;
            }
            fwrite($handle, "DROP TABLE IF EXISTS {$quoted};\n{$create};\n\n");
            foreach (DB::table($table)->cursor() as $row) {
                $values = (array) $row;
                $columns = implode(', ', array_map(fn ($key) => '`' . str_replace('`', '``', $key) . '`', array_keys($values)));
                $quotedValues = implode(', ', array_map(fn ($value) => $value === null ? 'NULL' : $connection->getPdo()->quote((string) $value), array_values($values)));
                fwrite($handle, "INSERT INTO {$quoted} ({$columns}) VALUES ({$quotedValues});\n");
            }
            fwrite($handle, "\n");
        }
        fwrite($handle, $driver === 'mysql' ? "SET FOREIGN_KEY_CHECKS=1;\n" : "PRAGMA foreign_keys = ON;\n");
        fclose($handle);
    }

    private function addDirectory(ZipArchive $zip, string $source, string $prefix): void
    {
        if (!File::isDirectory($source)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) $zip->addFile($file->getPathname(), $prefix . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1)));
        }
    }

    private function zipFiles(ZipArchive $zip): array
    {
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'files/') && !str_contains(substr($name, 6), '..')) $files[] = $name;
        }
        return $files;
    }

    private function runSql(string $sql): void
    {
        if (trim($sql) === '') throw new RuntimeException('File SQL backup trống.');
        DB::connection()->getPdo()->exec($sql);
    }

    private function replacePublicFiles(string $stage): void
    {
        $target = storage_path('app/public');
        $rollback = storage_path('app/backup-restore-rollback/' . now()->format('Ymd_His'));
        File::ensureDirectoryExists(dirname($rollback));
        if (File::exists($target)) File::move($target, $rollback);
        try {
            File::move($stage, $target);
        } catch (\Throwable $error) {
            if (File::exists($rollback)) File::move($rollback, $target);
            throw $error;
        }
    }

    private function requireZip(): void
    {
        if (!class_exists(ZipArchive::class)) throw new RuntimeException('Máy chủ chưa cài PHP extension zip.');
    }
}
