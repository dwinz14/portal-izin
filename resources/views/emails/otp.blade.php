<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP — {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #0B1426 0%, #1e3a5f 100%);
            padding: 36px 40px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 4px;
        }

        .body {
            padding: 40px;
        }

        .greeting {
            font-size: 15px;
            color: #475569;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .greeting strong {
            color: #1e293b;
        }

        .purpose-label {
            font-size: 13px;
            color: #64748b;
            text-align: center;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .otp-box {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px 20px;
            text-align: center;
            margin: 20px 0 28px;
        }

        .otp-code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #1e3a5f;
            font-family: 'Courier New', monospace;
        }

        .expire-note {
            font-size: 13px;
            color: #ef4444;
            margin-top: 12px;
            font-weight: 500;
        }

        .info-text {
            font-size: 13px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 8px;
        }

        .warning-box {
            background: #fef9c3;
            border-left: 4px solid #eab308;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 24px;
            font-size: 13px;
            color: #713f12;
            line-height: 1.6;
        }

        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 40px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
        }

        .footer strong {
            color: #475569;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Sistem Informasi Manajemen Izin Karyawan</p>
        </div>

        <div class="body">
            <p class="greeting">
                Halo, <strong>{{ ucwords($user->name) }}</strong>.
                @if ($purpose === 'verify_email')
                    Terima kasih telah mendaftar. Berikut adalah kode verifikasi email Anda.
                @else
                    Kami menerima permintaan reset password untuk akun Anda. Berikut kode OTP Anda.
                @endif
            </p>

            <p class="purpose-label">
                {{ $purpose === 'verify_email' ? 'Kode Verifikasi Email' : 'Kode Reset Password' }}
            </p>

            <div class="otp-box">
                <div class="otp-code">{{ $code }}</div>
                <p class="expire-note">Berlaku selama {{ $expireMinutes }} menit</p>
            </div>

            <p class="info-text">Masukkan kode 6 digit di atas pada halaman verifikasi. Jangan bagikan kode ini kepada
                siapapun.</p>
            <p class="info-text">Jika Anda tidak merasa melakukan tindakan ini, abaikan email ini.</p>

            <div class="warning-box">
                <strong>Perhatian:</strong> Tim {{ config('app.name') }} tidak akan pernah meminta kode OTP Anda
                melalui telepon, WhatsApp, atau media lainnya. Jaga kerahasiaan kode ini.
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem <strong>{{ config('app.name') }}</strong>.<br>Mohon jangan
                membalas email ini.</p>
        </div>
    </div>
</body>

</html>
