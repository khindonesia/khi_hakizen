<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket: {{ $event->title }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #0f1013;
            color: #1f2937;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #0f1013;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #1f2937;
        }
        .ticket-accent-bar {
            height: 6px;
            background-color: #df1c24;
            width: 100%;
        }
        .header {
            padding: 24px 32px;
            background-color: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            text-align: left;
        }
        .header-logo {
            height: 36px;
            vertical-align: middle;
            margin-right: 12px;
        }
        .header-org {
            display: inline-block;
            vertical-align: middle;
        }
        .header-org-name {
            font-size: 10px;
            font-weight: 800;
            color: #df1c24;
            letter-spacing: 0.1em;
            margin: 0;
            font-family: monospace;
        }
        .header-status-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #047857;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }
        .content {
            padding: 32px;
            background-color: #ffffff;
        }
        .event-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-top: 0;
            margin-bottom: 24px;
            line-height: 1.4;
        }
        .info-row {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-icon-cell {
            width: 40px;
            vertical-align: top;
        }
        .info-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: #fef2f2;
            text-align: center;
            line-height: 32px;
        }
        .info-icon {
            font-size: 16px;
            color: #df1c24;
            font-weight: bold;
        }
        .info-text-cell {
            vertical-align: middle;
            padding-left: 12px;
        }
        .info-label {
            font-size: 9px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin: 0;
        }
        .divider {
            border-top: 1px dashed #e5e7eb;
            margin: 24px 0;
            height: 0;
        }
        .attendee-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendee-cell {
            vertical-align: top;
        }
        .attendee-name {
            font-size: 14px;
            font-weight: 800;
            color: #111827;
            margin: 0;
        }
        .attendee-email {
            font-size: 11px;
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        .price-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .price-paid {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .price-free {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .footer {
            padding: 24px 32px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .footer-text {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }
        .footer-button {
            display: inline-block;
            background-color: #df1c24;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Top bar -->
            <div class="ticket-accent-bar"></div>

            <!-- Header -->
            <div class="header">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <img src="{{ url('/images/logo.jpg') }}" alt="KHI Logo" class="header-logo">
                            <div class="header-org">
                                <h3 class="header-org-name">KOMUNITAS HISTORIA INDONESIA</h3>
                                <span class="header-status-badge">Terkonfirmasi</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h1 class="event-title">{{ $event->title }}</h1>

                <!-- Date -->
                <table class="info-row">
                    <tr>
                        <td class="info-icon-cell">
                            <div class="info-icon-box">
                                <span class="info-icon">📅</span>
                            </div>
                        </td>
                        <td class="info-text-cell">
                            <span class="info-label">Hari & Tanggal</span>
                            <p class="info-value">
                                @if (method_exists($event->start_datetime, 'translatedFormat'))
                                    {{ $event->start_datetime->translatedFormat('l, d F Y') }}
                                @else
                                    {{ $event->start_datetime->format('d F Y') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Time -->
                <table class="info-row">
                    <tr>
                        <td class="info-icon-cell">
                            <div class="info-icon-box">
                                <span class="info-icon">⏰</span>
                            </div>
                        </td>
                        <td class="info-text-cell">
                            <span class="info-label">Waktu Acara</span>
                            <p class="info-value">{{ $event->start_datetime->format('H:i') }} - {{ $event->end_datetime->format('H:i') }} WIB</p>
                        </td>
                    </tr>
                </table>

                <!-- Location -->
                <table class="info-row">
                    <tr>
                        <td class="info-icon-cell">
                            <div class="info-icon-box">
                                <span class="info-icon">📍</span>
                            </div>
                        </td>
                        <td class="info-text-cell">
                            <span class="info-label">Lokasi Pertemuan</span>
                            <p class="info-value">{{ $event->location ?? 'Kota Tua Jakarta, DKI Jakarta' }}</p>
                        </td>
                    </tr>
                </table>

                <div class="divider"></div>

                <!-- Attendee Details -->
                <table class="attendee-table">
                    <tr>
                        <td class="attendee-cell" style="width: 50%;">
                            <span class="info-label">Nama Peserta</span>
                            <p class="attendee-name">{{ $user->name }}</p>
                            <p class="attendee-email">{{ $user->email }}</p>
                        </td>
                        @php
                            $refId = is_object($registration) ? ($registration->invoice_id ?? $registration->external_id ?? null) : ($registration['invoice_id'] ?? $registration['external_id'] ?? null);
                        @endphp
                        @if($refId)
                        <td class="attendee-cell" style="width: 25%; text-align: center;">
                            <span class="info-label">Ref Pembayaran</span>
                            <p class="info-value" style="font-family: monospace; font-size: 11px;">{{ $refId }}</p>
                        </td>
                        @endif
                        <td class="attendee-cell" style="width: 25%; text-align: right;">
                            <span class="info-label">Harga</span>
                            @if($event->type === 'PAID')
                                <span class="price-badge price-paid">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                            @else
                                <span class="price-badge price-free">Gratis</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer / Action -->
            <div class="footer">
                <a href="{{ route('dashboard.events') }}" class="footer-button">Buka Dashboard Saya</a>
                <p class="footer-text">Gunakan tiket digital ini saat registrasi ulang di lokasi pertemuan.<br>Terima kasih atas partisipasi Anda.</p>
            </div>
        </div>
    </div>
</body>
</html>
