<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectDiscussion extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'group_id',
        'user_id',
        'parent_id',
        'message',
        'mentions',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'mentions' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function group()
    {
        return $this->belongsTo(DiscussionGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectDiscussion::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ProjectDiscussion::class, 'parent_id')->with(['user', 'attachments', 'replies']);
    }

    public function attachments()
    {
        return $this->hasMany(ProjectDiscussionAttachment::class, 'project_discussion_id');
    }

    public function mentionedUsers()
    {
        if (empty($this->mentions)) {
            return collect();
        }
        return User::whereIn('id', $this->mentions)->get();
    }

    /**
     * Get users who have read this message (for the sender only)
     */
    public function getReadByAttribute()
    {
        $query = DiscussionRead::query();
        if ($this->project_id) {
            $query->where('project_id', $this->project_id);
        } else {
            $query->where('group_id', $this->group_id);
        }
        return $query->where('last_read_at', '>=', $this->created_at)
            ->with('user')
            ->get()
            ->pluck('user');
    }
}
