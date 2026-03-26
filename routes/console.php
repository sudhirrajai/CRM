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

Schedule::call(function () {
    $days = (int) Setting::getValue('invoice_due_reminder_days', 3);
    $targetDate = now()->addDays($days)->toFormattedDateString();
    
    $invoices = Invoice::where('status', '!=', 'paid')
        ->whereDate('due_date', now()->addDays($days)->toDateString())
        ->get();

    foreach ($invoices as $invoice) {
        Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
    }
})->dailyAt('09:00');
