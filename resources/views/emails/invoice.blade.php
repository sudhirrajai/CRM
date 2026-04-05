<x-mail::message>
# Dear {{ $invoice->client->name }},

We've generated a new invoice for your recent project/services. Please find the summary below and the full invoice attached as a PDF.

<x-mail::panel>
**Invoice Number:** #{{ $invoice->invoice_number }}<br>
**Total Amount:** <span style="color: #4f46e5; font-size: 1.1em; font-weight: bold;">{{ $invoice->currency->symbol }}{{ number_format($invoice->total_amount, 2) }}</span><br>
**Due Date:** <span style="color: #e11d48; font-weight: bold;">{{ $invoice->due_date->format('M d, Y') }}</span>
</x-mail::panel>

If you have any questions regarding this invoice, please feel free to reply directly to this email or contact support.

Thank you for your business!

Best Regards,<br>
**{{ config('app.name') }} Team**
</x-mail::message>
