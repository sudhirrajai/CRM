<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AutomationStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'automation_id',
        'order',
        'action_type',
        'email_template_id',
        'wait_duration',
        'condition_rules',
    ];

    protected function casts(): array
    {
        return [
            'condition_rules' => 'array',
        ];
    }

    public function automation()
    {
        return $this->belongsTo(MarketingAutomation::class, 'automation_id');
    }

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function enrollments()
    {
        return $this->hasMany(AutomationEnrollment::class, 'current_step_id');
    }

    /**
     * Get the next step in the automation sequence.
     */
    public function nextStep()
    {
        return AutomationStep::where('automation_id', $this->automation_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}
