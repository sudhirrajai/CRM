<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AutomationEnrollment extends Model
{
    use HasUuids;

    protected $fillable = [
        'automation_id',
        'enrollable_type',
        'enrollable_id',
        'current_step_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function automation()
    {
        return $this->belongsTo(MarketingAutomation::class, 'automation_id');
    }

    public function enrollable()
    {
        return $this->morphTo();
    }

    public function currentStep()
    {
        return $this->belongsTo(AutomationStep::class, 'current_step_id');
    }
}
