<x-mail::message>
# Invoice Payment Reminder

Dear {{ $invoice->client->name }},

This is a friendly reminder regarding your outstanding invoice **#{{ $invoice->invoice_number }}**.

<x-mail::panel>
**Amount Due:** {{ $amount }}<br>
**Due Date:** {{ $dueDate }}
</x-mail::panel>

<x-mail::button :url="route('invoices.show', $invoice->id)">
Pay Invoice Now
</x-mail::button>

Please ensure payment is made by the due date to avoid any potential service interruptions. If you have already made the payment, please ignore this email.

Thanks,<br>
**{{ config('app.name') }} Billing**
</x-mail::message>
