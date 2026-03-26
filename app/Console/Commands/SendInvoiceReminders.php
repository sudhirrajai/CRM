<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\ClientHosting;
use App\Mail\InvoiceReminderMail;
use App\Mail\HostingSuspensionMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

#[Signature('app:send-invoice-reminders')]
#[Description('Send invoice reminders and handle automated hosting suspension')]
class SendInvoiceReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Send Reminders (30, 15, 0 days)
        $invoices = Invoice::where('status', '!=', 'paid')
            ->whereIn('due_date', [
                $today->copy()->addDays(30),
                $today->copy()->addDays(15),
                $today
            ])->get();

        foreach ($invoices as $invoice) {
            $daysUntilDue = $today->diffInDays($invoice->due_date, false);
            $reminderType = $daysUntilDue == 30 ? '30_days' : ($daysUntilDue == 15 ? '15_days' : 'due_date');

            if ($invoice->last_reminder_type !== $reminderType) {
                Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
                $invoice->update(['last_reminder_type' => $reminderType]);
                $this->info("Sent {$reminderType} reminder for Invoice #{$invoice->invoice_number}");
            }
        }

        // 2. Automated Suspension (7 days after due date)
        $overdueInvoices = Invoice::where('status', '!=', 'paid')
            ->where('due_date', $today->copy()->subDays(7))
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $hosting = ClientHosting::where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('status', 'active')
                ->first();

            if ($hosting) {
                $hosting->update(['status' => 'suspended']);
                Mail::to($invoice->client->email)->send(new HostingSuspensionMail($invoice, $hosting));
                $this->warn("Suspended hosting for Invoice #{$invoice->invoice_number} (7 days overdue)");
            }
        }

        $this->info('Invoice reminders and suspension checks completed.');
    }
}
