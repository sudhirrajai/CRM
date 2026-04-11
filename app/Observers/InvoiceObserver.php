<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\AutomationTriggerService;

class InvoiceObserver
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function created(Invoice $invoice): void
    {
        $this->triggerService->fire('invoice.created', $invoice);
    }
}
