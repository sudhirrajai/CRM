<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body { width: 100% !important; }
            .footer { width: 100% !important; }
        }
    </style>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; width: 100%;">
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f8fafc; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Email Header -->
                    <tr>
                        <td class="header" style="padding: 25px 0; text-align: center;">
                            <a href="{{ config('app.url') }}" style="display: inline-block;">
                                @php
                                    $logoPath = public_path('assets/images/vmcore.png');
                                    if (file_exists($logoPath)) {
                                        $logoData = base64_encode(file_get_contents($logoPath));
                                        $logoSrc = 'data:image/png;base64,' . $logoData;
                                    } else {
                                        $logoSrc = asset('assets/images/vmcore.png'); // Fallback
                                    }
                                @endphp
                                <img src="{{ $logoSrc }}" alt="VMCore Logo" style="height: 48px; max-width: 200px;">
                            </a>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; margin: 0; padding: 0; width: 100%;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; margin: 0 auto; padding: 35px 0; width: 570px;">
                                <tr>
                                    <td class="content-cell" style="padding: 35px;">
                                        <h1 style="color: #0f172a; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;">Dear {{ $invoice->client->name }},</h1>
                                        <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left; color: #475569;">
                                            We've generated a new invoice for your recent project/services. Please find the summary below and the full invoice attached as a PDF.
                                        </p>

                                        <!-- Invoice Details -->
                                        <table class="details" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 30px 0; padding: 20px; border-radius: 8px; background-color: #f1f5f9;">
                                            <tr>
                                                <td style="padding: 10px 0;">
                                                    <table width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="color: #64748b; font-size: 13px; text-transform: uppercase;">Invoice Number</td>
                                                            <td align="right" style="color: #0f172a; font-weight: bold;">#{{ $invoice->invoice_number }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 10px 0; color: #64748b; font-size: 13px; text-transform: uppercase;">Total Amount</td>
                                                            <td align="right" style="padding: 10px 0; color: #4f46e5; font-size: 18px; font-weight: bold;">{{ $invoice->currency->symbol }}{{ number_format($invoice->total_amount, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="color: #64748b; font-size: 13px; text-transform: uppercase;">Due Date</td>
                                                            <td align="right" style="color: #e11d48; font-weight: bold;">{{ $invoice->due_date->format('M d, Y') }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left; color: #475569;">
                                            If you have any questions regarding this invoice, please feel free to reply directly to this email or contact support.
                                        </p>

                                        <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left; color: #475569;">
                                            Thank you for your business!
                                        </p>

                                        <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left; color: #475569;">
                                            Best Regards,<br>
                                            <strong>The VMCore Team</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center">
                            <table class="footer" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; padding: 32px 0; text-align: center; width: 570px;">
                                <tr>
                                    <td class="content-cell" align="center" style="padding: 35px;">
                                        <p style="color: #94a3b8; font-size: 12px; text-align: center;">
                                            &copy; {{ date('Y') }} VMCore. All rights reserved.
                                        </p>
                                        <div style="margin-top: 10px;">
                                            <a href="{{ config('app.url') }}" style="color: #4f46e5; font-size: 12px; text-decoration: none;">Visit Website</a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
