<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order status update</title>
    <style>
        body { font-family: Poppins, Arial, sans-serif; background: #FFFDF7; color: #0A0A0A; margin: 0; padding: 32px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid rgba(10,10,10,.08); border-radius: 20px; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .brand { color: #D50808; font-weight: 700; letter-spacing: .06em; }
        .meta { color: #6B6B6B; font-size: 14px; line-height: 1.7; }
        .status { display: inline-block; margin-top: 12px; background: #D50808; color: #fff; padding: 8px 18px; border-radius: 999px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        .btn { display: inline-block; margin-top: 24px; background: #D50808; color: #fff; text-decoration: none; padding: 13px 26px; border-radius: 999px; font-size: 14px; font-weight: 700; }
        .order-no { font-family: monospace; font-size: 16px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <p class="brand">RHYTHM EXPORTS</p>
        <h1>Your order just moved 🎵</h1>

        <p class="meta">
            Order <span class="order-no">{{ $order->order_number }}</span> is now:
        </p>
        <span class="status">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>

        <p class="meta" style="margin-top:20px">
            @if($status === \App\Models\Order::STATUS_SHIPPED)
                Your instruments are on the way! You'll receive tracking details as soon as the courier picks them up.
            @elseif($status === \App\Models\Order::STATUS_DELIVERED)
                Your order has been delivered. We hope it sounds amazing — don't forget to leave a review!
            @elseif($status === \App\Models\Order::STATUS_CANCELLED)
                This order was cancelled. If you didn't request this, contact us within 24 hours.
            @else
                We're working on your order — stay tuned for the next update.
            @endif
        </p>

        <a class="btn" href="{{ $trackUrl }}">Track your order</a>

        <p class="meta" style="margin-top:24px;font-size:11px">
            Questions? Reply to this email — we usually answer within a few hours.
        </p>
    </div>
</body>
</html>
