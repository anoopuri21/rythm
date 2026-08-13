<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order confirmed</title>
    <style>
        body { font-family: Poppins, Arial, sans-serif; background: #FFFDF7; color: #0A0A0A; margin: 0; padding: 32px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid rgba(10,10,10,.08); border-radius: 20px; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .brand { color: #D50808; font-weight: 700; letter-spacing: .06em; }
        .meta { color: #6B6B6B; font-size: 13px; line-height: 1.7; }
        .order-no { font-family: monospace; font-size: 18px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 10px 0; border-bottom: 1px solid rgba(10,10,10,.06); font-size: 14px; }
        .total td { border-bottom: none; padding-top: 16px; font-weight: 700; font-size: 16px; }
        .btn { display: inline-block; margin-top: 24px; background: #D50808; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 999px; font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <p class="brand">RHYTHM MUSIC STORE</p>
        <h1>Thank you, {{ $order->shipping_address['name'] ?? 'there' }}! 🎸</h1>
        <p class="meta">Your order <span class="order-no">{{ $order->order_number }}</span> is confirmed.
            We've started packing your instruments.</p>

        <table>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->name }}</strong><br>
                        <span class="meta">SKU {{ $item->sku }} · Qty {{ $item->qty }}</span>
                    </td>
                    <td align="right">₹{{ number_format((float) $item->total) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total paid ({{ $order->currency }})</td>
                <td align="right">₹{{ $total }}</td>
            </tr>
        </table>

        <p class="meta" style="margin-top:20px">
            Delivering to:<br>
            <strong>{{ $order->shipping_address['name'] ?? '' }}</strong><br>
            {{ $order->shipping_address['line1'] ?? '' }}<br>
            {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}
        </p>

        <a class="btn" href="{{ url('/') }}">Continue shopping</a>
        <p class="meta" style="margin-top:24px;font-size:11px">Questions? Reply to this email — we usually answer within a few hours.</p>
    </div>
</body>
</html>
