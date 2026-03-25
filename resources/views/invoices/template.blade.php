<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2),
        .invoice-box table tr td:nth-child(3),
        .invoice-box table tr td:nth-child(4) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2),
        .invoice-box table tr.total td:nth-child(3),
        .invoice-box table tr.total td:nth-child(4) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mb-1 { margin-bottom: 5px; }
        .mt-4 { margin-top: 20px; }
        .text-muted { color: #777; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                <div class="company-name">CRM PLATFORM</div>
                                <div style="font-size: 14px; color: #555; margin-top: 5px;">Professional Services</div>
                            </td>
                            
                            <td class="text-right">
                                <h1 style="color: #4f46e5; margin: 0; font-size: 36px; text-transform: uppercase;">INVOICE</h1>
                                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                                <strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}<br>
                                <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}<br>
                                <strong>Status:</strong> <span style="text-transform: uppercase;">{{ $invoice->status }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                <strong>Billed To:</strong><br>
                                {{ $invoice->client->name }}<br>
                                @if($invoice->client->company)
                                    {{ $invoice->client->company }}<br>
                                @endif
                                {{ $invoice->client->email }}<br>
                                {{ $invoice->client->phone }}<br>
                                {{ $invoice->client->address }}
                            </td>
                            
                            <td class="text-right">
                                <strong>From:</strong><br>
                                CRM Team<br>
                                support@crmplatform.com<br>
                                123 Business Avenue,<br>
                                Tech City, TC 12345
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>Item Description</td>
                <td class="text-right">Quantity</td>
                <td class="text-right">Unit Price</td>
                <td class="text-right">Amount</td>
            </tr>

            @if($invoice->items && $invoice->items->count() > 0)
                @foreach($invoice->items as $item)
                <tr class="item {{ $loop->last ? 'last' : '' }}">
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr class="item last">
                    <td>Services Rendered</td>
                    <td class="text-right">1</td>
                    <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($invoice->sub_total, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($invoice->sub_total, 2) }}</td>
                </tr>
            @endif

            <tr class="total">
                <td colspan="2"></td>
                <td class="text-right">Subtotal:</td>
                <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($invoice->sub_total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="text-right">Tax:</td>
                <td class="text-right">{{ $invoice->currency->symbol }}{{ number_format($invoice->tax, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="2"></td>
                <td class="text-right" style="font-size: 20px;">Total:</td>
                <td class="text-right" style="font-size: 20px;">{{ $invoice->currency->symbol }}{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </table>

        @if($invoice->notes)
        <div class="mt-4">
            <strong>Notes / Terms:</strong>
            <p class="text-muted" style="white-space: pre-line;">{{ $invoice->notes }}</p>
        </div>
        @endif
        
        <div class="mt-4 text-center text-muted" style="font-size: 13px; border-top: 1px solid #eee; padding-top: 15px;">
            Thank you for your business! If you have any questions regarding this invoice, please contact us.
        </div>
    </div>
</body>
</html>
