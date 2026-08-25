<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentionGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_by'];

    public function members()
    {
        return $this->belongsToMany(User::class, 'mention_group_user')->withTimestamps();
    }
}
