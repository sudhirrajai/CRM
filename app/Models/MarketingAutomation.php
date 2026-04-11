<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MarketingAutomation extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'trigger_conditions',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger_conditions' => 'array',
        ];
    }

    public const TRIGGER_EVENTS = [
        'lead.created' => 'Lead Created',
        'lead.stage_changed' => 'Lead Stage Changed',
        'lead.converted' => 'Lead Converted to Client',
        'client.created' => 'Client Created',
        'invoice.created' => 'Invoice Created',
        'invoice.overdue' => 'Invoice Overdue',
        'ticket.created' => 'Ticket Created',
        'project.created' => 'Project Created',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(AutomationStep::class, 'automation_id')->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(AutomationEnrollment::class, 'automation_id');
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function getTriggerLabelAttribute()
    {
        return self::TRIGGER_EVENTS[$this->trigger_event] ?? $this->trigger_event;
    }
}
