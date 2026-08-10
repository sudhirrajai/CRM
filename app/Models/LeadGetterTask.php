<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadGetterTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'lead_getter_group_id',
        'query',
        'location',
        'api_provider',
        'filters',
        'status',
        'total_results',
        'error_message',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function group()
    {
        return $this->belongsTo(LeadGetterGroup::class, 'lead_getter_group_id');
    }

    public function results()
    {
        return $this->hasMany(LeadGetterResult::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
