<?php

return [
    'enabled' => env('BACKUP_ENABLED', false),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'rclone_binary' => env('RCLONE_BINARY', 'rclone'),
    'rclone_remote' => env('RCLONE_REMOTE'),
    'rclone_timeout' => (int) env('RCLONE_TIMEOUT', 3600),
    'max_import_bytes' => (int) env('BACKUP_IMPORT_MAX_BYTES', 5 * 1024 * 1024 * 1024),
];
