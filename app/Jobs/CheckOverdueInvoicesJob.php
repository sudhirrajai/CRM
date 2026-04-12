<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\AutomationTriggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOverdueInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AutomationTriggerService $triggerService): void
    {
        // Find invoices that are past due and haven't been flagged as overdue for automation
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->whereNull('last_reminder_type')
            ->orWhere(function ($q) {
                $q->where('status', 'unpaid')
                  ->where('due_date', '<', now())
                  ->where('last_reminder_type', '!=', 'automation_overdue');
            })
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $triggerService->fire('invoice.overdue', $invoice);

            $invoice->update(['last_reminder_type' => 'automation_overdue']);
        }
    }
}
