<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'title',
        'lead_id',
        'created_by',
        'hidden_from_admin',
        'health',
        'tracking_status',
        'is_pinned',
        'sort_order',
        'last_activity_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'hidden_from_admin' => 'boolean',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSystemAdmin()) {
            return $query;
        }

        if ($user->is_admin) {
            return $query->where('hidden_from_admin', false);
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('lead_id', $user->id)
                ->orWhereHas('members', function (Builder $members) use ($user) {
                    $members->where('users.id', $user->id);
                });
        });
    }

    public function isVisibleTo(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isSystemAdmin()
            || ($user->is_admin && !$this->hidden_from_admin)
            || (int) $this->created_by === (int) $user->id
            || (int) $this->lead_id === (int) $user->id
            || $this->members()->where('users.id', $user->id)->exists();
    }

    public function canManageMembers(?User $user): bool
    {
        return $user && ($user->isSystemAdmin()
            || ($user->is_admin && !$this->hidden_from_admin)
            || (int) $this->created_by === (int) $user->id
            || (int) $this->lead_id === (int) $user->id);
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

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->where('users.is_admin', false)
            ->withTimestamps();
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
