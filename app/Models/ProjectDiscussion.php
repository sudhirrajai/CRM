<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectDiscussion extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'user_id',
        'parent_id',
        'message',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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
}
