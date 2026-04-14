<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\Invoice;
use App\Models\Setting;
use App\Mail\InvoiceReminderMail;
use Illuminate\Support\Facades\Mail;
use App\Support\InvoiceRecipientResolver;

Schedule::call(function () {
    $recipientResolver = app(InvoiceRecipientResolver::class);
    $days = (int) Setting::getValue('invoice_due_reminder_days', 3);
    $targetDate = now()->addDays($days)->toFormattedDateString();
    
    $invoices = Invoice::where('status', '!=', 'paid')
        ->whereDate('due_date', now()->addDays($days)->toDateString())
        ->get();

    foreach ($invoices as $invoice) {
        $recipients = $recipientResolver->emails($invoice);
        if (!empty($recipients)) {
            Mail::to($recipients)->send(new InvoiceReminderMail($invoice));
        }
    }
})->dailyAt('09:00');
