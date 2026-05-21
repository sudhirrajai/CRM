<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DiscussionGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'created_by',
    ];

    /**
     * Get the members of the discussion group.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'discussion_group_members', 'group_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get the discussions / messages in this group.
     */
    public function discussions()
    {
        return $this->hasMany(ProjectDiscussion::class, 'group_id');
    }

    /**
     * Get the user who created this group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
