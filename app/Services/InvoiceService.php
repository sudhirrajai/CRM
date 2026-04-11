<?php

namespace App\Services;

use App\Models\Invoice;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Send an invoice to all designated recipients (Primary client + project members with send_invoice flag).
     */
    public function sendToRecipients(Invoice $invoice): void
    {
        $invoice->load(['client', 'project.invoiceRecipients', 'currency', 'items']);
        
        $recipients = collect([$invoice->client->email]);
        
        if ($invoice->project) {
            foreach ($invoice->project->invoiceRecipients as $recipient) {
                $recipients->push($recipient->email);
            }
        }

        $recipients = $recipients->unique()->filter();

        if ($recipients->isEmpty()) {
            return;
        }

        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        $pdfOutput = $pdf->output();

        foreach ($recipients as $email) {
            Mail::to($email)->send(new InvoiceMail($invoice, $pdfOutput));
        }

        // Mark as auto-sent if it's the first time
        if (empty($invoice->last_reminder_type)) {
            $invoice->update(['last_reminder_type' => 'auto_sent']);
        }
    }
}
