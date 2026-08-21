<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Invoice - {{ $conference->title ?? 'ICHA' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 30px 10px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #3b0764 0%, #1e1b4b 100%);
            padding: 36px 28px;
            text-align: center;
            color: #ffffff;
        }
        .header-badge {
            display: inline-block;
            background-color: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.35);
            font-weight: 800;
            font-size: 11px;
            padding: 4px 14px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.3;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 13px;
            color: #cbd5e1;
        }
        .body {
            padding: 32px 28px;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            font-weight: 800;
            font-size: 11px;
            padding: 5px 14px;
            border-radius: 9999px;
            text-transform: uppercase;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        .status-paid {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .status-unpaid {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-review {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .salutation {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .intro-text {
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .card-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table td {
            padding: 9px 0;
            font-size: 13px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .label {
            color: #64748b;
            font-weight: 500;
        }
        .val {
            text-align: right;
            font-weight: 700;
            color: #1e293b;
        }
        .bank-box {
            background: #faf5ff;
            border: 1px solid #f3e8ff;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .success-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .review-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .bank-name {
            font-size: 14px;
            font-weight: 800;
            color: #581c87;
            margin: 0 0 8px 0;
        }
        .acc-number {
            font-size: 18px;
            font-weight: 900;
            color: #3b0764;
            font-family: 'Courier New', Courier, monospace;
            background: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px dashed #d8b4fe;
            display: inline-block;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .steps-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .step-item {
            display: flex;
            margin-bottom: 10px;
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }
        .step-num {
            background-color: #3b0764;
            color: #ffffff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 20px;
            font-size: 11px;
            font-weight: 800;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0 12px 0;
        }
        .btn {
            display: inline-block;
            background-color: #fbbf24;
            color: #0f172a !important;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.25);
            transition: all 0.2s ease;
        }
        .btn-green {
            background-color: #10b981;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .footer {
            background: #f8fafc;
            padding: 24px 28px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <span class="header-badge">Official Conference Invoice</span>
                <h1>{{ $conference->title ?? 'International Conference on Healthcare Administration (ICHA)' }}</h1>
                <p>Registration Billing & Payment Summary</p>
            </div>

            <!-- Body -->
            <div class="body">
                @php
                    $status = strtolower($registration->status ?? 'unpaid');
                @endphp

                @if($status === 'paid')
                    <span class="status-pill status-paid">✅ PAID / LUNAS</span>
                @elseif($status === 'under_review')
                    <span class="status-pill status-review">🔍 UNDER REVIEW / MENUNGGU VERIFIKASI</span>
                @elseif($status === 'rejected')
                    <span class="status-pill status-rejected">❌ REJECTED / PEMBAYARAN DITOLAK</span>
                @else
                    <span class="status-pill status-unpaid">⏳ UNPAID / MENUNGGU PEMBAYARAN</span>
                @endif

                <h2 class="salutation">Dear {{ $user->name }},</h2>
                <p class="intro-text">
                    @if($status === 'paid')
                        Your registration and payment for <strong>{{ $conference->title ?? 'ICHA Conference' }}</strong> have been <strong>successfully verified and confirmed</strong>. Below is your official invoice and payment receipt breakdown.
                    @elseif($status === 'under_review')
                        Thank you for submitting your payment proof for <strong>{{ $conference->title ?? 'ICHA Conference' }}</strong>. Your submission is currently under review by our finance committee.
                    @else
                        Thank you for registering for <strong>{{ $conference->title ?? 'ICHA Conference' }}</strong>. Please find your official invoice information and payment instructions below.
                    @endif
                </p>

                <!-- Invoice Summary Card -->
                <div class="card">
                    <div class="card-title">Invoice Summary</div>
                    <table class="table">
                        <tr>
                            <td class="label">Invoice Number</td>
                            <td class="val" style="color: #3b0764; font-size: 14px; font-family: monospace;">{{ $registration->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">Date Issued</td>
                            <td class="val">{{ $registration->created_at ? $registration->created_at->format('M d, Y H:i') : now()->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Participant Name</td>
                            <td class="val">{{ $user->name }}</td>
                        </tr>
                        @if($user->profile && $user->profile->institution)
                        <tr>
                            <td class="label">Institution / Affiliation</td>
                            <td class="val">{{ $user->profile->institution }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Registration Package</td>
                            <td class="val">{{ $fee->name ?? ($registration->registrationFee->name ?? 'Participant') }}</td>
                        </tr>
                        @if($fee && $fee->mode)
                        <tr>
                            <td class="label">Attendance Mode</td>
                            <td class="val" style="text-transform: capitalize;">{{ $fee->mode }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Rate Type</td>
                            <td class="val">{{ $registration->is_early_bird ? 'Early Bird Rate' : 'Regular Rate' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Payment Status</td>
                            <td class="val">
                                @if($status === 'paid')
                                    <span style="color: #16a34a; font-weight: 800;">PAID & VERIFIED</span>
                                @elseif($status === 'under_review')
                                    <span style="color: #0284c7; font-weight: 800;">UNDER REVIEW</span>
                                @elseif($status === 'rejected')
                                    <span style="color: #dc2626; font-weight: 800;">REJECTED</span>
                                @else
                                    <span style="color: #d97706; font-weight: 800;">UNPAID</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label" style="font-weight: 800; font-size: 14px; color: #1e293b; padding-top: 14px;">Total Amount</td>
                            <td class="val" style="font-weight: 900; font-size: 18px; color: #3b0764; padding-top: 14px;">
                                {{ $registration->currency }} {{ number_format($registration->amount) }}
                            </td>
                        </tr>
                    </table>
                </div>

                @if($status === 'paid')
                    <!-- Paid Confirmation Box -->
                    <div class="success-box">
                        <div style="font-weight: 800; font-size: 15px; color: #166534; margin-bottom: 6px;">🎉 Registration Confirmed & Active</div>
                        <p style="margin: 0; font-size: 13px; color: #15803d; line-height: 1.6;">
                            Your conference seat is officially secured. You can now access full participant privileges, download conference schedules, and participate in all conference sessions.
                        </p>
                    </div>

                    <!-- CTA Button -->
                    <div class="btn-container">
                        <a href="{{ url('/my/registration') }}" class="btn btn-green">
                            View Registration & Portal →
                        </a>
                    </div>
                @elseif($status === 'under_review')
                    <!-- Under Review Box -->
                    <div class="review-box">
                        <div style="font-weight: 800; font-size: 14px; color: #075985; margin-bottom: 6px;">🔍 Payment Proof Submitted</div>
                        <p style="margin: 0; font-size: 13px; color: #0369a1; line-height: 1.6;">
                            We have received your payment proof and our finance team is currently validating the transaction. You will receive an official payment confirmation email once verified.
                        </p>
                    </div>

                    <!-- CTA Button -->
                    <div class="btn-container">
                        <a href="{{ url('/my/registration') }}" class="btn">
                            Check Verification Status →
                        </a>
                    </div>
                @else
                    <!-- Bank Transfer Details Card (For Unpaid) -->
                    <div class="bank-box">
                        <div class="card-title" style="color: #6b21a8; border-color: #f3e8ff;">Payment Instructions (Bank Transfer)</div>
                        <p style="font-size: 13px; color: #475569; margin: 0 0 10px 0;">Please transfer the exact total amount to our official committee account:</p>
                        
                        <div>
                            <div class="bank-name">{{ $conference->bank_name ?? 'Bank Syariah Indonesia (BSI)' }}</div>
                            <div class="acc-number">{{ $conference->bank_account_number ?? '7192837465' }}</div>
                            <p style="font-size: 12px; color: #475569; margin: 4px 0 0 0;">
                                Account Name: <strong>{{ $conference->bank_account_holder ?? 'PANITIA ICHA PIPMARSI' }}</strong>
                            </p>
                            @if(!empty($conference->bank_instructions))
                            <p style="font-size: 11px; color: #64748b; margin: 6px 0 0 0; font-style: italic;">
                                {{ $conference->bank_instructions }}
                            </p>
                            @endif
                        </div>

                        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #d8b4fe; font-size: 11px; color: #6b21a8;">
                            💡 <strong>Important:</strong> Please write your Invoice Number <strong style="font-family: monospace;">{{ $registration->invoice_number }}</strong> in the transfer remarks/description.
                        </div>
                    </div>

                    <!-- Steps -->
                    <div class="steps-box">
                        <div class="card-title" style="margin-bottom: 8px;">Next Steps to Confirm Registration</div>
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 28px; vertical-align: top; padding-top: 2px;">
                                    <span class="step-num">1</span>
                                </td>
                                <td style="font-size: 12px; color: #475569; padding-bottom: 8px;">
                                    <strong>Complete Payment:</strong> Transfer the exact amount of <strong>{{ $registration->currency }} {{ number_format($registration->amount) }}</strong> to the bank account above.
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 28px; vertical-align: top; padding-top: 2px;">
                                    <span class="step-num">2</span>
                                </td>
                                <td style="font-size: 12px; color: #475569; padding-bottom: 8px;">
                                    <strong>Upload Receipt:</strong> Save your transaction receipt (JPG/PNG/PDF) and upload it to the Participant Portal.
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 28px; vertical-align: top; padding-top: 2px;">
                                    <span class="step-num">3</span>
                                </td>
                                <td style="font-size: 12px; color: #475569;">
                                    <strong>Verification:</strong> Our committee will review and verify your payment within 1-2 business days.
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- CTA Button -->
                    <div class="btn-container">
                        <a href="{{ url('/my/registration') }}" class="btn">
                            Upload Payment Proof & View Invoice →
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0; font-weight: 700; color: #334155;">International Conference on Healthcare Administration (ICHA)</p>
                <p style="margin: 4px 0 0 0;">Need assistance? Contact our committee at <a href="mailto:conference.icha10@gmail.com" style="color: #3b0764; text-decoration: underline;">conference.icha10@gmail.com</a></p>
                <p style="margin: 8px 0 0 0; font-size: 10px; color: #94a3b8;">This is an automated system notification. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
