<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'assignee_id',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'health',
        'is_private',
        'private_user_ids',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_private' => 'boolean',
        'private_user_ids' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($task) {
            $extracted = app(\App\ProjectMemberService::class)->extractPrivateMentionUserIds(
                $task->title,
                is_array($task->private_user_ids) ? $task->private_user_ids : []
            );
            if (!empty($extracted)) {
                $task->is_private = true;
                $task->private_user_ids = array_values(array_unique($extracted));
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
