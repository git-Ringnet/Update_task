# System backups

## Upload limit for restore

The restore endpoint accepts archives up to 5 GB by default. The web server must permit an upload larger than that archive. This project includes `public/.user.ini` with a 6 GB PHP upload/post limit for PHP-FPM/CGI deployments. Restart PHP-FPM (or wait for its `.user.ini` cache to refresh) after deploying it. If Nginx is used, also set `client_max_body_size 6G;` in the server block.

`backup:system` creates a ZIP archive containing `database.sql`, a manifest, and everything under `storage/app/public` (attachments and avatars).

## Manual backup and restore

System Administrators can use **Tải backup hệ thống** and **Khôi phục backup** in the application menu. Restoring replaces the database and all public uploads; the previously active uploads are retained temporarily under `storage/app/backup-restore-rollback` for manual recovery.

For the command line:

```bash
php artisan backup:system
php artisan backup:system --upload
```

## Google Drive / rclone

Install rclone on the server and run `rclone config` to create a Google Drive remote. Configure the production `.env`:

```env
BACKUP_ENABLED=true
BACKUP_RETENTION_DAYS=30
RCLONE_BINARY=/usr/bin/rclone
RCLONE_REMOTE="gdrive:xuong-rong-backups"
```

Run Laravel's scheduler every minute (the backup command itself runs hourly):

```cron
* * * * * cd /var/www/xuongrong/backend && php artisan schedule:run >> /dev/null 2>&1
```

The scheduled task creates a local ZIP, uploads it to the configured rclone remote, then removes local and remote `system_backup_*.zip` files older than 30 days. Check the cron user's permissions for `storage/app/backups`, `storage/app/public`, and the rclone config directory.
