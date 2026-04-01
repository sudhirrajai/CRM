<x-mail::message>
# Service Suspension Notice

Dear {{ $invoice->client->name }},

Your hosting services for **{{ $hosting->domain }}** have been suspended as of today due to an unpaid invoice **#{{ $invoice->invoice_number }}**.

@if($reason)
<x-mail::panel>
**Reason:** {{ $reason }}
</x-mail::panel>
@endif

**IMPORTANT:** Please settle your due within **3 days** to avoid permanent service termination.

<x-mail::button :url="route('invoices.show', $invoice->id)">
Pay Outstanding Balance
</x-mail::button>

If you have already paid, please ignore this email. For any issues, contact our billing department.

Thanks,<br>
**{{ config('app.name') }} Infrastructure**
</x-mail::message>
