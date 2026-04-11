<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AutoSendDueInvoicesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\InvoiceService $invoiceService): void
    {
        $invoices = \App\Models\Invoice::where('status', 'unpaid')
            ->whereDate('due_date', now()->toDateString())
            ->where(function($q) {
                $q->whereNull('last_reminder_type')
                  ->orWhere('last_reminder_type', '!=', 'auto_sent');
            })
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceService->sendToRecipients($invoice);
        }
    }
}
