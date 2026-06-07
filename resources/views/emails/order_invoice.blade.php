<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_id }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #fff8f7;
            color: #281716;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #fff8f7;
            padding: 24px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #fbdbd9;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(40, 23, 22, 0.04);
        }
        .header {
            background-color: #ffffff;
            padding: 32px;
            border-bottom: 3px solid #a80017;
            text-align: left;
        }
        .header-logo {
            height: 48px;
            vertical-align: middle;
            margin-right: 12px;
        }
        .header-title {
            display: inline-block;
            font-size: 20px;
            font-weight: 700;
            color: #a80017;
            vertical-align: middle;
            margin: 0;
        }
        .content {
            padding: 32px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            color: #a80017;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .details-grid {
            width: 100%;
            margin-bottom: 32px;
            border-collapse: collapse;
        }
        .details-col {
            width: 50%;
            vertical-align: top;
            padding: 12px;
            background-color: #fff0ef;
            border: 1px solid #fbdbd9;
            border-radius: 8px;
        }
        .details-col-right {
            width: 50%;
            vertical-align: top;
            padding: 12px;
            background-color: #ffe9e7;
            border: 1px solid #fbdbd9;
            border-radius: 8px;
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #a80017;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .detail-text {
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #5c403d;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        .items-table th {
            padding: 12px;
            font-size: 11px;
            font-weight: 700;
            color: #a80017;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #a80017;
            text-align: left;
        }
        .items-table td {
            padding: 16px 12px;
            font-size: 14px;
            border-bottom: 1px solid #fbdbd9;
            color: #281716;
        }
        .item-name {
            font-weight: 600;
            margin: 0;
        }
        .item-meta {
            font-size: 11px;
            color: #5c403d;
            margin: 4px 0 0 0;
        }
        .summary-box {
            width: 280px;
            margin-left: auto;
            background-color: #ffffff;
            border: 1px solid #fbdbd9;
            border-radius: 8px;
            padding: 16px;
        }
        .summary-row {
            width: 100%;
            margin-bottom: 12px;
        }
        .summary-row td {
            font-size: 13px;
            color: #5c403d;
        }
        .summary-row td.amount {
            text-align: right;
            color: #281716;
            font-weight: 500;
        }
        .summary-row-total {
            width: 100%;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px solid #a80017;
        }
        .summary-row-total td {
            font-size: 16px;
            font-weight: 700;
            color: #281716;
        }
        .summary-row-total td.amount {
            text-align: right;
            color: #a80017;
        }
        .footer {
            padding: 32px;
            background-color: #ffffff;
            border-top: 1px solid #fbdbd9;
            text-align: center;
        }
        .footer-text {
            font-size: 13px;
            color: #5c403d;
            font-style: italic;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <img src="{{ url('/images/logo.jpg') }}" alt="KHI Logo" class="header-logo">
                <h2 class="header-title">Komunitas Historia Indonesia</h2>
            </div>

            <!-- Content -->
            <div class="content">
                <h1 class="invoice-title">Invoice #{{ $order->invoice_id }}</h1>

                <!-- Details Grid -->
                <table class="details-grid">
                    <tr>
                        <!-- Shipping info -->
                        <td class="details-col">
                            <h3 class="section-title">Alamat Pengiriman</h3>
                            <p class="detail-text" style="font-weight: 600; margin-bottom: 4px;">{{ $order->user?->name }}</p>
                            <p class="detail-text" style="color: #5c403d;">
                                @if ($order->address)
                                    {{ $order->address->address_line }}<br/>
                                    @if ($order->address->village || $order->address->district)
                                        {{ $order->address->village }}{{ $order->address->village && $order->address->district ? ', ' : '' }}{{ $order->address->district }}<br/>
                                    @endif
                                    {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}<br/>
                                    {{ $order->address->country }}
                                @else
                                    Tidak ada alamat pengiriman.
                                @endif
                            </p>
                            @if ($order->user?->phone)
                                <p class="detail-text" style="margin-top: 8px;"><span class="detail-label">Telp:</span> {{ $order->user->phone }}</p>
                            @endif
                        </td>
                        <!-- Spacer -->
                        <td style="width: 16px;"></td>
                        <!-- Order details -->
                        <td class="details-col-right">
                            <h3 class="section-title">Detail Pesanan</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td class="detail-text" style="padding-bottom: 6px;"><span class="detail-label">Tanggal:</span></td>
                                    <td class="detail-text" style="text-align: right; padding-bottom: 6px;">{{ $order->created_at->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="detail-text" style="padding-bottom: 6px;"><span class="detail-label">Status:</span></td>
                                    <td class="detail-text" style="text-align: right; padding-bottom: 6px; font-weight: 600; color: #107f5b;">Paid</td>
                                </tr>
                                <tr>
                                    <td class="detail-text" style="padding-bottom: 6px;"><span class="detail-label">Metode:</span></td>
                                    <td class="detail-text" style="text-align: right; padding-bottom: 6px;">
                                        @if($order->external_id)
                                            Virtual Account
                                        @else
                                            Manual Transfer
                                        @endif
                                    </td>
                                </tr>
                                @if ($order->courier)
                                <tr>
                                    <td class="detail-text"><span class="detail-label">Kurir:</span></td>
                                    <td class="detail-text" style="text-align: right; text-transform: uppercase;">{{ $order->courier }} ({{ $order->service }})</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Produk</th>
                            <th style="width: 15%; text-align: center;">Qty</th>
                            <th style="width: 15%; text-align: right;">Harga</th>
                            <th style="width: 20%; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <p class="item-name">{{ $item->product?->name ?? 'Product Unavailable' }}</p>
                                @if ($item->variant && !$item->variant->is_default)
                                    @php
                                        $attributes = [];
                                        foreach ($item->variant->variantAttributes as $va) {
                                            if ($va->attribute && $va->attributeValue) {
                                                $attributes[] = $va->attribute->name . ': ' . $va->attributeValue->value;
                                            }
                                        }
                                        $variantDesc = implode(', ', $attributes);
                                    @endphp
                                    @if (!empty($variantDesc))
                                        <p class="item-meta">{{ $variantDesc }}</p>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: 500;">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Summary Box -->
                <table class="summary-box" align="right">
                    <tr class="summary-row">
                        <td>Subtotal</td>
                        <td class="amount">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Ongkos Kirim</td>
                        <td class="amount">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="summary-row-total">
                        <td style="font-weight: 700; padding-top: 12px;">Grand Total</td>
                        <td class="amount" style="font-weight: 700; padding-top: 12px;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
                <div style="clear: both;"></div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-text">Terima kasih atas pesanan Anda. Dukungan Anda sangat berarti dalam membantu kami melestarikan sejarah Indonesia.</p>
            </div>
        </div>
    </div>
</body>
</html>
