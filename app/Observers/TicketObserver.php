<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\AutomationTriggerService;

class TicketObserver
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function created(Ticket $ticket): void
    {
        $this->triggerService->fire('ticket.created', $ticket);
    }
}
