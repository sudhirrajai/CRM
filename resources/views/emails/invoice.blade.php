<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Hello {{ $invoice->client->name }},</h2>
    <p>Please find attached the invoice <strong>#{{ $invoice->invoice_number }}</strong> for your recent project/services.</p>
    
    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
    <p><strong>Total Amount:</strong> {{ $invoice->currency->symbol }}{{ number_format($invoice->total_amount, 2) }}</p>

    <p>If you have any questions or concerns, please do not hesitate to contact us by replying directly to this email.</p>

    <br>
    <p>Best regards,<br>
    CRM Team</p>
</body>
</html>
