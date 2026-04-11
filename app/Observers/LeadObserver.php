<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\AutomationTriggerService;

class LeadObserver
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function created(Lead $lead): void
    {
        $this->triggerService->fire('lead.created', $lead);
    }

    public function updated(Lead $lead): void
    {
        // Check for stage change
        if ($lead->isDirty('lead_pipeline_stage_id')) {
            $this->triggerService->fire('lead.stage_changed', $lead, [
                'old_stage_id' => $lead->getOriginal('lead_pipeline_stage_id'),
                'new_stage_id' => $lead->lead_pipeline_stage_id,
            ]);
        }

        // Check for conversion
        if ($lead->isDirty('converted_client_id') && $lead->converted_client_id) {
            $this->triggerService->fire('lead.converted', $lead);
        }
    }
}
