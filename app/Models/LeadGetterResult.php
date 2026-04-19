<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadGetterResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'lead_getter_task_id',
        'title',
        'company',
        'contact_name',
        'email',
        'phone',
        'website',
        'address',
        'rating',
        'reviews_count',
        'category',
        'raw_data',
        'status',
        'qualified_at',
        'lead_id',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'rating' => 'decimal:1',
            'qualified_at' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(LeadGetterTask::class, 'lead_getter_task_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    public function scopeDisqualified($query)
    {
        return $query->where('status', 'disqualified');
    }
}
