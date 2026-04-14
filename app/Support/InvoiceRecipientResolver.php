<?php

namespace App\Support;

use App\Models\Invoice;

class InvoiceRecipientResolver
{
    public function emails(Invoice $invoice): array
    {
        $invoice->loadMissing(['client:id,email', 'sharedClients:id,email', 'emailRecipients:id,invoice_id,email']);

        return collect([
            $invoice->client?->email,
            ...$invoice->sharedClients->pluck('email')->all(),
            ...$invoice->emailRecipients->pluck('email')->all(),
        ])
            ->filter()
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
