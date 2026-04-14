<x-mail::message>
@php
    $brandName = \App\Models\Setting::getValue('brand_name', config('app.name'));
    $contactEmail = \App\Models\Setting::getValue('contact_email', config('mail.from.address'));
    $contactPhone = \App\Models\Setting::getValue('contact_phone', '');
    $contactAddress = \App\Models\Setting::getValue('contact_address', '');
@endphp

Hi {{ $invoice->client->name }},

Your invoice is ready. We have attached the PDF for your records and included a quick summary below.

<x-mail::panel>
<strong style="font-size: 16px; color: #0f172a;">Invoice #{{ $invoice->invoice_number }}</strong><br>
<span style="color: #475569;">Issued: {{ $invoice->issue_date?->format('M d, Y') ?? '-' }}</span><br>
<span style="color: #475569;">Due: {{ $invoice->due_date?->format('M d, Y') ?? '-' }}</span><br><br>
<span style="color: #475569;">Total Amount</span><br>
<span style="color: #4f46e5; font-size: 22px; font-weight: 700;">
    {{ $invoice->currency->symbol }}{{ number_format($invoice->total_amount, 2) }}
</span>
</x-mail::panel>

@if($invoice->items && $invoice->items->count())
<x-mail::table>
| Service | Qty | Rate | Amount |
|:--------|:---:|-----:|-------:|
@foreach($invoice->items as $item)
| {{ $item->description }} | {{ $item->quantity }} | {{ $invoice->currency->symbol }}{{ number_format($item->unit_price, 2) }} | {{ $invoice->currency->symbol }}{{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>
@endif

Please review the attached invoice and process payment by the due date to avoid service interruption.

If anything needs correction, simply reply to this email and our team will assist you.

<p style="font-size: 13px; color: #64748b; margin-top: 20px;">
{{ $brandName }}<br>
@if($contactEmail){{ $contactEmail }}<br>@endif
@if($contactPhone){{ $contactPhone }}<br>@endif
@if($contactAddress){!! nl2br(e($contactAddress)) !!}@endif
</p>

Regards,<br>
<strong>{{ $brandName }} Billing Team</strong>
</x-mail::message>
