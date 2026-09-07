<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - ICHA 10th</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #1e135e 0%, #291a7b 60%, #3b0764 100%);
            padding: 36px 24px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            color: #FACE68;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 8px 0 0 0;
            font-size: 13px;
            color: #e9d5ff;
            font-weight: 500;
        }

        .body {
            padding: 36px 28px;
        }

        .badge {
            display: inline-block;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 800;
            font-size: 11px;
            padding: 5px 14px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin: 24px 0;
        }

        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #FACE68 0%, #f59e0b 100%);
            color: #0f172a !important;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            padding: 14px 34px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(250, 206, 104, 0.4);
            letter-spacing: 0.3px;
        }

        .security-notice {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 12px;
            color: #854d0e;
            line-height: 1.6;
            margin-top: 24px;
        }

        .fallback-link {
            word-break: break-all;
            font-size: 11px;
            color: #64748b;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            line-height: 1.6;
        }

        .footer {
            background: #f1f5f9;
            padding: 24px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer a {
            color: #291a7b;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/logo/logo-icha.png') }}" alt="ICHA 10th Logo" style="height: 52px; width: auto; margin-bottom: 12px; display: inline-block;">
            <br>
            <h1>ICHA 10th (2026)</h1>
            <p>10th International Conference on Hospital Administration</p>
        </div>

        <div class="body">
            <span class="badge">🔒 Password Reset Request</span>

            <p style="font-size: 16px; font-weight: 700; margin: 0 0 12px 0; color: #0f172a;">
                Hello, {{ $user->name }}
            </p>

            <p style="font-size: 14px; color: #475569; line-height: 1.65; margin: 0 0 16px 0;">
                You are receiving this email because we received a password reset request for your account on the <strong>ICHA 2026 Conference Portal</strong>.
            </p>

            <p style="font-size: 14px; color: #475569; line-height: 1.65; margin: 0;">
                To choose a new password and regain access to your conference account, please click the button below:
            </p>

            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset My Password</a>
            </div>

            <div class="security-notice">
                <strong>⏱️ Important Note:</strong> This password reset link is only valid for <strong>{{ $count }} minutes</strong>. If you did not request a password reset, please ignore this email or contact the committee if you have security concerns.
            </div>

            <div class="fallback-link">
                If you are having trouble clicking the "Reset My Password" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" style="color: #291a7b;">{{ $resetUrl }}</a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0; font-weight: 700; color: #334155;">
                International Conference on Hospital Administration (ICHA 10th)
            </p>
            <p style="margin: 6px 0 0 0;">
                Need help? Reach us at <a href="mailto:conference.icha10@gmail.com">conference.icha10@gmail.com</a>
            </p>
            <p style="margin: 6px 0 0 0; color: #94a3b8;">
                &copy; {{ date('Y') }} ICHA Committee. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>
