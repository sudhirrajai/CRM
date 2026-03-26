<x-mail::message>
# Service Suspension Warning

Dear {{ $invoice->client->name }},

Your hosting services for **{{ $hosting->domain }}** have been suspended as of today due to an unpaid invoice **#{{ $invoice->invoice_number }}**.

@if($reason)
**Reason:** {{ $reason }}
@endif

**Please pay your due within 3 days else the service will be terminated.**

<x-mail::button :url="route('invoices.show', $invoice->id)">
Pay Now
</x-mail::button>

If you have already paid, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
