<?php

namespace App\Services;

use App\Models\MarketingAutomation;
use App\Models\AutomationEnrollment;
use App\Jobs\ProcessAutomationStepJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AutomationTriggerService
{
    /**
     * Fire a trigger event and enroll matching entities in active automations.
     */
    public function fire(string $event, Model $entity, array $context = []): void
    {
        $automations = MarketingAutomation::where('trigger_event', $event)
            ->where('status', 'active')
            ->has('steps')
            ->get();

        foreach ($automations as $automation) {
            // Check if conditions match
            if (!$this->conditionsMatch($automation, $entity, $context)) {
                continue;
            }

            // Check if already enrolled
            $alreadyEnrolled = AutomationEnrollment::where('automation_id', $automation->id)
                ->where('enrollable_type', get_class($entity))
                ->where('enrollable_id', $entity->getKey())
                ->where('status', 'active')
                ->exists();

            if ($alreadyEnrolled) {
                continue;
            }

            // Get first step
            $firstStep = $automation->steps()->orderBy('order')->first();
            if (!$firstStep) continue;

            // Create enrollment
            $enrollment = AutomationEnrollment::create([
                'automation_id' => $automation->id,
                'enrollable_type' => get_class($entity),
                'enrollable_id' => $entity->getKey(),
                'current_step_id' => $firstStep->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            // Dispatch first step
            ProcessAutomationStepJob::dispatch($enrollment, $firstStep);

            Log::info("Enrolled {$enrollment->enrollable_type}:{$enrollment->enrollable_id} in automation: {$automation->name}");
        }
    }

    /**
     * Check if automation conditions match the event context.
     */
    private function conditionsMatch(MarketingAutomation $automation, Model $entity, array $context): bool
    {
        $conditions = $automation->trigger_conditions;

        if (empty($conditions)) {
            return true;
        }

        // Check stage condition for leads
        if (isset($conditions['stage_id']) && $entity instanceof \App\Models\Lead) {
            if ($entity->lead_pipeline_stage_id !== $conditions['stage_id']) {
                return false;
            }
        }

        // Check new stage condition (for stage_changed event)
        if (isset($conditions['new_stage_id']) && isset($context['new_stage_id'])) {
            if ($context['new_stage_id'] !== $conditions['new_stage_id']) {
                return false;
            }
        }

        // Check source condition
        if (isset($conditions['source']) && $entity instanceof \App\Models\Lead) {
            if ($entity->source !== $conditions['source']) {
                return false;
            }
        }

        return true;
    }
}
