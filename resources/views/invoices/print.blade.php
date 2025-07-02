<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->invoice_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .customer-info, .order-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="invoice-title">INVOICE</div>
        <div>Invoice #{{ $order->invoice_id }}</div>
        <div>{{ $order->created_at->format('d F Y') }}</div>
    </div>
    
    <div class="company-info">
        <strong>Your Company Name</strong><br>
        Company Address Line 1<br>
        Company Address Line 2<br>
        Phone: (123) 456-7890<br>
        Email: info@yourcompany.com
    </div>
    
    <div class="row">
        <div class="customer-info">
            <strong>Bill To:</strong><br>
            {{ $order->user->name }}<br>
            {{ $order->address->address }}<br>
            {{ $order->address->city }}, {{ $order->address->postal_code }}<br>
            Phone: {{ $order->user->phone }}<br>
            Email: {{ $order->user->email }}
        </div>
        
        <div class="order-info">
            <strong>Order Details:</strong><br>
            Order Date: {{ $order->created_at->format('d/m/Y') }}<br>
            Payment Status: {{ ucfirst($order->payment_status) }}<br>
            Order Status: {{ ucfirst($order->status) }}<br>
            @if($order->external_id)
            Payment Ref: {{ $order->external_id }}<br>
            @endif
            Shipping: {{ $order->courier }} ({{ $order->service }})
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Subtotal:</td>
                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Shipping Fee:</td>
                <td class="text-right">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice. No signature is required.</p>
    </div>
</body>
</html>