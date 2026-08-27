<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Services\ProjectPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendProjectCommentPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct(public int $commentId)
    {
    }

    public function handle(ProjectPushService $pushService): void
    {
        $comment = Comment::find($this->commentId);
        if ($comment) {
            $pushService->sendForComment($comment);
        }
    }
}
