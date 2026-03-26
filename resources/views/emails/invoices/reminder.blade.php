<x-mail::message>
# Invoice Due Reminder

Dear {{ $client->name }},

# Invoice Reminder

Dear {{ $invoice->client->name }},

This is a friendly reminder regarding your invoice **#{{ $invoice->invoice_number }}**.

**Amount Due:** {{ $amount }}
**Due Date:** {{ $dueDate }}

<x-mail::button :url="route('invoices.show', $invoice->id)">
View Invoice
</x-mail::button>

Please ensure payment is made by the due date to avoid any service interruptions.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
