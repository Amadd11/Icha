<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Re-upload Required - ICHA</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { background: #3b0764; padding: 32px 24px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 6px 0 0 0; font-size: 13px; color: #e9d5ff; }
        .body { padding: 32px 24px; }
        .badge { display: inline-block; background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 800; font-size: 11px; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; margin-bottom: 16px; }
        .reason-card { background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 18px; margin: 20px 0; color: #9f1239; font-size: 13px; }
        .btn { display: inline-block; background-color: #dc2626; color: #ffffff; font-weight: 800; font-size: 13px; text-decoration: none; padding: 12px 28px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .footer { background: #f1f5f9; padding: 20px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $conference->title ?? 'ICHA Conference' }}</h1>
            <p>Payment Proof Verification Notice</p>
        </div>
        <div class="body">
            <span class="badge">⚠️ Re-upload Required</span>
            
            <p style="font-size: 15px; font-weight: 700; margin-top: 0;">Dear {{ $user->name }},</p>
            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                Thank you for submitting your payment proof for invoice <strong>#{{ $registration->invoice_number }}</strong>. However, the committee was unable to verify your payment with the document provided.
            </p>

            <div class="reason-card">
                <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; color: #be123c;">Reason from Committee:</strong>
                {{ $payment->rejection_reason ?? 'The submitted payment proof file is illegible or does not match the invoice amount.' }}
            </div>

            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                Please log in to your participant account and re-upload a clear copy of your payment receipt to complete your registration.
            </p>

            <div style="text-align: center;">
                <a href="{{ route('participant.registration.create') }}" class="btn">Re-upload Payment Proof →</a>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0; font-weight: 700; color: #334155;">International Conference on Healthcare Administration (ICHA)</p>
            <p style="margin: 4px 0 0 0;">Need assistance? Contact our committee at <a href="mailto:conference.icha10@gmail.com" style="color: #3b0764; text-decoration: underline;">conference.icha10@gmail.com</a></p>
            <p style="margin: 6px 0 0 0;">This is an automated system notification. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
