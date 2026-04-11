<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\AutomationTriggerService;

class ProjectObserver
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function created(Project $project): void
    {
        $this->triggerService->fire('project.created', $project);
    }
}
