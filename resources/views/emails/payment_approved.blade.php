<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Payment Receipt - ICHA</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { background: #3b0764; padding: 32px 24px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 6px 0 0 0; font-size: 13px; color: #e9d5ff; }
        .body { padding: 32px 24px; }
        .badge { display: inline-block; background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-weight: 800; font-size: 11px; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; margin-bottom: 16px; }
        .invoice-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table td { padding: 8px 0; font-size: 13px; border-bottom: 1px dashed #e2e8f0; }
        .table tr:last-child td { border-bottom: none; }
        .btn { display: inline-block; background-color: #fbbf24; color: #0f172a; font-weight: 800; font-size: 13px; text-decoration: none; padding: 12px 28px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .footer { background: #f1f5f9; padding: 20px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $conference->title ?? 'ICHA Conference' }}</h1>
            <p>Official Payment Receipt & Verified Invoice</p>
        </div>
        <div class="body">
            <span class="badge">✓ Payment Verified & Approved</span>
            
            <p style="font-size: 15px; font-weight: 700; margin-top: 0;">Dear {{ $user->name }},</p>
            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                We are pleased to inform you that your payment proof for <strong>{{ $conference->title ?? 'ICHA' }}</strong> has been verified and approved by the committee.
            </p>

            <div class="invoice-card">
                <table class="table">
                    <tr>
                        <td style="color: #64748b;">Invoice Number</td>
                        <td style="font-weight: 800; text-align: right; color: #3b0764;">{{ $registration->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">Registration Category</td>
                        <td style="font-weight: 700; text-align: right;">{{ $registration->registrationType->name ?? ($registration->registration_type->name ?? 'Participant') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">Rate Type</td>
                        <td style="font-weight: 700; text-align: right;">{{ $registration->is_early_bird ? 'Early Bird' : 'Regular' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">Payment Date</td>
                        <td style="font-weight: 700; text-align: right;">{{ $payment->verified_at ? $payment->verified_at->format('M d, Y H:i') : now()->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 700; font-size: 14px;">Total Amount Paid</td>
                        <td style="font-weight: 900; font-size: 16px; text-align: right; color: #16a34a;">
                            {{ $payment->currency }} {{ number_format($payment->amount) }}
                        </td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                Your registration is now official. You can access your participant portal to view your invoice, upload presentation files, and check upcoming schedules.
            </p>

            <div style="text-align: center;">
                <a href="{{ url('/participant/dashboard') }}" class="btn">Open Participant Portal →</a>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0;">International Conference on Healthcare Administration (ICHA)</p>
            <p style="margin: 4px 0 0 0;">This is an automated system notification. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
