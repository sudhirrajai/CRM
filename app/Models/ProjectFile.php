<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'path',
        'size',
        'mime_type',
        'share_token',
        'shared_at',
        'expires_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'expires_at' => 'datetime',
        'size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the file share link is currently valid.
     */
    public function isShareValid(): bool
    {
        if (!$this->share_token) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
