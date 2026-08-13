<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'title',
        'lead_id',
        'health',
        'tracking_status',
        'is_pinned',
        'sort_order',
        'last_activity_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComment()
    {
        return $this->hasOne(Comment::class)->latestOfMany();
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('created_at', 'asc');
    }

    public function isPinnedForUser(?int $userId): bool
    {
        if (!$userId || !$this->id) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('pinned_projects')
            ->where('user_id', $userId)
            ->where('project_id', $this->id)
            ->exists();
    }

    public function applyPinnedStateForUser(?int $userId): self
    {
        $this->setAttribute('is_pinned', $this->isPinnedForUser($userId));
        return $this;
    }
}
