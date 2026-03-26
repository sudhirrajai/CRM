<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectDiscussionAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_discussion_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function discussion()
    {
        return $this->belongsTo(ProjectDiscussion::class, 'project_discussion_id');
    }
}
