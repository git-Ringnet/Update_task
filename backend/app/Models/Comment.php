<?php

namespace App\Models;

use App\Jobs\SendProjectCommentPush;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'content',
        'type',
        'project_health',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::creating(function ($comment) {
            if ($comment->project_id && !$comment->project_health) {
                $project = \App\Models\Project::find($comment->project_id);
                if ($project) {
                    $comment->project_health = $project->health;
                }
            }
        });

        static::created(function (Comment $comment) {
            // Web Push can involve dozens of external Apple/FCM/WNS requests.
            // Dispatch after response so the client request is never delayed
            // while still guaranteeing execution even when queue workers are not running.
            $baseUrl = request()->hasHeader('host') ? request()->getSchemeAndHttpHost() : config('app.url');
            SendProjectCommentPush::dispatchAfterResponse($comment->id, $baseUrl);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
