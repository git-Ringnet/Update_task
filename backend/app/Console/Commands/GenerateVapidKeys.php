<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:generate-vapid {--force : Replace existing VAPID keys}';

    protected $description = 'Generate and save a VAPID key pair for browser push notifications';

    public function handle(): int
    {
        $envPath = base_path('.env');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';
        $hasKeys = preg_match('/^VAPID_PUBLIC_KEY=.+$/m', $contents)
            && preg_match('/^VAPID_PRIVATE_KEY=.+$/m', $contents);

        if ($hasKeys && !$this->option('force')) {
            $this->info('VAPID keys are already configured. Use --force to replace them.');
            return self::SUCCESS;
        }

        $keys = VAPID::createVapidKeys();
        $contents = $this->putEnvValue($contents, 'VAPID_SUBJECT', env('VAPID_SUBJECT', 'mailto:admin@xuongrong.vn'));
        $contents = $this->putEnvValue($contents, 'VAPID_PUBLIC_KEY', $keys['publicKey']);
        $contents = $this->putEnvValue($contents, 'VAPID_PRIVATE_KEY', $keys['privateKey']);

        file_put_contents($envPath, rtrim($contents).PHP_EOL);
        $this->info('VAPID keys were saved to .env. Keep the private key secret.');

        return self::SUCCESS;
    }

    private function putEnvValue(string $contents, string $key, string $value): string
    {
        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';

        return preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents)
            : rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }
}
