<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Project;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class ProjectPushService
{
    public function sendForComment(Comment $comment): void
    {
        $project = $comment->project()->with('members')->first();
        if (!$project || !$this->isConfigured()) {
            return;
        }

        $adminIds = \App\Models\User::query()
            ->where('is_system_admin', true)
            ->when(!$project->hidden_from_admin, function ($users) {
                $users->orWhere('is_admin', true);
            })
            ->pluck('id');

        $recipientIds = $project->members->pluck('id')
            ->push($project->created_by)
            ->push($project->lead_id)
            ->concat($adminIds)
            ->filter()
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $comment->user_id)
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $subscriptions = PushSubscription::whereIn('user_id', $recipientIds)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        $project->loadMissing('customer');
        $comment->loadMissing('user');

        $customerName = $project->customer?->name;
        $title = $customerName ?: 'Xương Rồng';

        $userName = $comment->user?->name ?? 'Một thành viên';
        $projectTitle = $project->title;
        $content = trim(preg_replace('/<[^>]*>/', ' ', strip_tags($comment->content)) ?? '');
        $content = preg_replace('/^\[reply:\{.*?\}\]\s*/i', '', $content);
        // Web Push payloads are limited to roughly 4 KB. Attachments and long
        // updates must not make the entire notification fail.
        $content = Str::limit($content, 500);
        $body = "{$projectTitle}\n{$userName}: " . ($content ?: 'vừa cập nhật dự án.');

        $icon = url('cactus-logo-square.png');

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'url' => '/projects/'.$project->id,
            'tag' => 'project-'.$project->id,
        ], JSON_UNESCAPED_UNICODE);

        try {
            $clientOptions = [];
            $caCertPath = config('webpush.ca_cert_path');
            if ($caCertPath && is_file($caCertPath)) {
                $clientOptions['verify'] = $caCertPath;
            }

            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('webpush.subject'),
                    'publicKey' => config('webpush.public_key'),
                    'privateKey' => config('webpush.private_key'),
                ],
            ], ['TTL' => 300, 'urgency' => 'high'], 10, $clientOptions);

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding,
                    ]),
                    $payload,
                    ['topic' => 'project-'.$project->id]
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
                if (!$report->isSuccess()) {
                    Log::warning('Project web push delivery was rejected.', [
                        'comment_id' => $comment->id,
                        'endpoint' => $report->getEndpoint(),
                        'reason' => $report->getReason(),
                        'status' => $report->getResponse()?->getStatusCode(),
                    ]);
                }
            }
        } catch (\Throwable $error) {
            Log::warning('Unable to send project web push notification.', [
                'comment_id' => $comment->id,
                'message' => $error->getMessage(),
            ]);
        }
    }

    private function isConfigured(): bool
    {
        return filled(config('webpush.subject'))
            && filled(config('webpush.public_key'))
            && filled(config('webpush.private_key'));
    }
}
