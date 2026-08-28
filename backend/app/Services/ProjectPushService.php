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
    public function sendForComment(Comment $comment, ?string $baseUrl = null): void
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

        $base = $baseUrl ?: config('app.url');
        $avatar = $comment->user?->avatar;
        if ($avatar) {
            if (str_starts_with($avatar, 'data:image/')) {
                try {
                    $user = $comment->user;
                    $data = $avatar;
                    list($type, $data) = explode(';', $data);
                    list(, $data)      = explode(',', $data);
                    $decodedData = base64_decode($data);

                    $extension = 'png';
                    if (str_contains($type, 'jpeg') || str_contains($type, 'jpg')) {
                        $extension = 'jpg';
                    } elseif (str_contains($type, 'gif')) {
                        $extension = 'gif';
                    }

                    if (function_exists('imagecreatefromstring')) {
                        $img = @imagecreatefromstring($decodedData);
                        if ($img) {
                            $width = imagesx($img);
                            $height = imagesy($img);
                            $maxDim = 150;
                            if ($width > $maxDim || $height > $maxDim) {
                                $ratio = $width / $height;
                                if ($ratio > 1) {
                                    $newWidth = $maxDim;
                                    $newHeight = (int)($maxDim / $ratio);
                                } else {
                                    $newHeight = $maxDim;
                                    $newWidth = (int)($maxDim * $ratio);
                                }
                                $newImg = imagecreatetruecolor($newWidth, $newHeight);
                                imagealphablending($newImg, false);
                                imagesavealpha($newImg, true);
                                imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                ob_start();
                                imagepng($newImg, null, 9);
                                $resizedData = ob_get_clean();
                                if ($resizedData) {
                                    $decodedData = $resizedData;
                                    $extension = 'png';
                                }
                                imagedestroy($newImg);
                            }
                            imagedestroy($img);
                        }
                    }

                    $fileName = 'avatars/user_' . $user->id . '_' . time() . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decodedData);
                    $newAvatarUrl = \Illuminate\Support\Facades\Storage::url($fileName);
                    
                    $user->avatar = $newAvatarUrl;
                    $user->save();
                    
                    $avatar = $newAvatarUrl;
                } catch (\Throwable $e) {
                    $avatar = null;
                }
            }

            if ($avatar && !str_starts_with($avatar, 'data:image/')) {
                $icon = (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://'))
                    ? $avatar
                    : rtrim($base, '/') . '/' . ltrim($avatar, '/');
            } else {
                $icon = rtrim($base, '/') . '/cactus-logo-square.png';
            }
        } else {
            $icon = rtrim($base, '/') . '/cactus-logo-square.png';
        }

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
