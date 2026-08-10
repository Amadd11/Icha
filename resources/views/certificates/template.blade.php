<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Certificate - {{ $certificate->certificate_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Montserrat:wght@400;600;700;800&family=Pinyon+Script&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-h-screen;
            padding: 20px;
        }
        .cert-container {
            width: 1056px;
            height: 746px;
            background: #ffffff;
            position: relative;
            padding: 25px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        .outer-border {
            border: 4px solid #4c1d95;
            height: 100%;
            padding: 8px;
            position: relative;
        }
        .inner-border {
            border: 2px solid #FACE68;
            height: 100%;
            padding: 30px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            position: relative;
            background: radial-gradient(circle at center, #ffffff 0%, #fafafa 100%);
        }
        .corner-decoration {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 3px solid #FACE68;
        }
        .top-left { top: 15px; left: 15px; border-right: none; border-bottom: none; }
        .top-right { top: 15px; right: 15px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 15px; left: 15px; border-right: none; border-top: none; }
        .bottom-right { bottom: 15px; right: 15px; border-left: none; border-top: none; }

        .header-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .header-logos img {
            height: 45px;
            object-fit: contain;
        }
        .cert-title {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 900;
            color: #4c1d95;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .cert-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #d97706;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .presented-to {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 15px;
        }
        .recipient-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 52px;
            color: #1e1b4b;
            margin: 10px 0;
            border-bottom: 2px solid #FACE68;
            padding-bottom: 5px;
            display: inline-block;
            min-width: 450px;
        }
        .role-badge {
            font-size: 14px;
            font-weight: 800;
            color: #4c1d95;
            background: rgba(250, 206, 104, 0.2);
            padding: 6px 24px;
            border-radius: 20px;
            border: 1px solid #FACE68;
            display: inline-block;
            margin: 5px 0 15px 0;
        }
        .cert-body {
            font-size: 12px;
            color: #475569;
            max-width: 750px;
            line-height: 1.6;
        }
        .conf-title-text {
            font-weight: 800;
            color: #0f172a;
        }
        .signatures {
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            margin-top: 20px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            width: 180px;
            border-bottom: 1.5px solid #94a3b8;
            margin-bottom: 6px;
        }
        .sig-name {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
        }
        .sig-title {
            font-size: 10px;
            color: #64748b;
        }
        .cert-footer {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
            margin-top: 10px;
        }

        .no-print-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            gap: 10px;
        }
        .btn-print {
            background: #FACE68;
            color: #0f172a;
            font-weight: 800;
            font-size: 13px;
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .cert-container {
                box-shadow: none;
                width: 100vw;
                height: 100vh;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print / Download PDF</button>
    </div>

    <div class="cert-container">
        <div class="outer-border">
            <div class="inner-border">
                <div class="corner-decoration top-left"></div>
                <div class="corner-decoration top-right"></div>
                <div class="corner-decoration bottom-left"></div>
                <div class="corner-decoration bottom-right"></div>

                <!-- Header Logos -->
                <div class="header-logos">
                    <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI">
                    <img src="/assets/logo/logo-umsura.png" alt="UMSURA">
                </div>

                <!-- Title -->
                <div>
                    <h1 class="cert-title">Certificate of Appreciation</h1>
                    <p class="cert-subtitle">International Conference on Healthcare Administration</p>
                </div>

                <!-- Presentation -->
                <div>
                    <p class="presented-to">This is to certify that</p>
                    <h2 class="recipient-name">{{ $certificate->user->name }}</h2>
                    <br>
                    <div class="role-badge">AS {{ strtoupper($certificate->role_title ?? 'PARTICIPANT') }}</div>
                </div>

                <!-- Body -->
                <p class="cert-body">
                    has actively participated in the <span class="conf-title-text">{{ $certificate->conference->title ?? 'ICHA 2026 Conference' }}</span> with the theme 
                    <em>"{{ $certificate->conference->theme ?? 'Healthcare Administration for a Sustainable Future' }}"</em> held on {{ date('d F Y', strtotime($certificate->issued_at)) }}.
                </p>

                <!-- Signatures -->
                <div class="signatures">
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <p class="sig-name">Prof. Dr. MARSI</p>
                        <p class="sig-title">Steering Committee Chairman</p>
                    </div>
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <p class="sig-name">Dr. UMSURA</p>
                        <p class="sig-title">Conference Director</p>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="cert-footer">
                    <span>Certificate No: <strong>{{ $certificate->certificate_number }}</strong></span>
                    <span>Issued Date: {{ date('Y-m-d', strtotime($certificate->issued_at)) }}</span>
                    <span>Verify at: https://icha.conference.id/verify</span>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Auto trigger print on page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>

</body>
</html>
