<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\AutomationTriggerService;

class ClientObserver
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function created(Client $client): void
    {
        $this->triggerService->fire('client.created', $client);
    }
}
