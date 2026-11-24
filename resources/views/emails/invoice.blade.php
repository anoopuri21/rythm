<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#FFFDF7;font-family:Arial,Helvetica,sans-serif;color:#0A0A0A;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFDF7;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border:1px solid rgba(10,10,10,.08);border-radius:20px;overflow:hidden;">
                    {{-- Header --}}
                    <tr>
                        <td style="padding:32px 32px 24px;border-bottom:1px solid rgba(10,10,10,.08);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:2px;color:#D4A843;text-transform:uppercase;">Tax Invoice</p>
                                        <p style="margin:6px 0 0;font-size:20px;font-weight:700;color:#0A0A0A;">{{ $businessName }}</p>
                                        @if($businessAddress)
                                            <p style="margin:4px 0 0;font-size:12px;color:#6B6B6B;line-height:1.5;">{{ $businessAddress }}</p>
                                        @endif
                                        @if($gstin)
                                            <p style="margin:2px 0 0;font-size:11px;color:#6B6B6B;">GSTIN: {{ $gstin }}</p>
                                        @endif
                                    </td>
                                    <td align="right" valign="top">
                                        <p style="margin:0;font-size:11px;color:#6B6B6B;text-transform:uppercase;letter-spacing:1px;">Invoice</p>
                                        <p style="margin:4px 0 0;font-size:16px;font-weight:700;font-family:monospace;color:#0A0A0A;">{{ $order->order_number }}</p>
                                        <p style="margin:6px 0 0;font-size:12px;color:#6B6B6B;">{{ $order->placed_at?->format('d M Y') }}</p>
                                        <p style="margin:2px 0 0;font-size:11px;color:#22C55E;font-weight:700;">{{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bill To --}}
                    <tr>
                        <td style="padding:20px 32px;border-bottom:1px solid rgba(10,10,10,.06);">
                            <p style="margin:0 0 4px;font-size:10px;font-weight:700;letter-spacing:2px;color:#6B6B6B;text-transform:uppercase;">Billed to</p>
                            <p style="margin:0;font-size:14px;font-weight:700;color:#0A0A0A;">{{ $order->shipping_address['name'] ?? '' }}</p>
                            <p style="margin:3px 0 0;font-size:13px;color:#444;line-height:1.5;">
                                {{ $order->shipping_address['line1'] ?? '' }}
                                @if(!empty($order->shipping_address['line2'])), {{ $order->shipping_address['line2'] }}@endif<br>
                                {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}
                            </p>
                            <p style="margin:3px 0 0;font-size:12px;color:#6B6B6B;">{{ $order->shipping_address['phone'] ?? '' }} · {{ $order->email }}</p>
                        </td>
                    </tr>

                    {{-- Items Table --}}
                    <tr>
                        <td style="padding:20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr style="border-bottom:2px solid rgba(10,10,10,.1);">
                                        <th align="left" style="padding:8px 0;font-size:10px;font-weight:700;letter-spacing:1px;color:#6B6B6B;text-transform:uppercase;">Item</th>
                                        <th align="center" style="padding:8px 0;font-size:10px;font-weight:700;letter-spacing:1px;color:#6B6B6B;text-transform:uppercase;">Qty</th>
                                        <th align="right" style="padding:8px 0;font-size:10px;font-weight:700;letter-spacing:1px;color:#6B6B6B;text-transform:uppercase;">Rate</th>
                                        <th align="right" style="padding:8px 0;font-size:10px;font-weight:700;letter-spacing:1px;color:#6B6B6B;text-transform:uppercase;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr style="border-bottom:1px solid rgba(10,10,10,.05);">
                                            <td style="padding:12px 0;vertical-align:top;">
                                                <p style="margin:0;font-size:13px;font-weight:600;color:#0A0A0A;">{{ $item->name }}</p>
                                                <p style="margin:2px 0 0;font-size:11px;color:#6B6B6B;">{{ $item->sku }}@if(!empty($item->options['finish'])) · {{ $item->options['finish'] }}@endif</p>
                                            </td>
                                            <td align="center" style="padding:12px 0;font-size:13px;color:#444;">{{ $item->qty }}</td>
                                            <td align="right" style="padding:12px 0;font-size:13px;color:#444;">{{ $currencySymbol }}{{ number_format((float) $item->unit_price, 2) }}</td>
                                            <td align="right" style="padding:12px 0;font-size:13px;font-weight:700;color:#0A0A0A;">{{ $currencySymbol }}{{ number_format((float) $item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    {{-- Totals --}}
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table role="presentation" width="260" cellpadding="0" cellspacing="0" align="right" style="width:260px;">
                                <tr>
                                    <td style="padding:6px 0;font-size:13px;color:#6B6B6B;">Subtotal</td>
                                    <td align="right" style="padding:6px 0;font-size:13px;font-weight:600;color:#0A0A0A;">{{ $currencySymbol }}{{ number_format((float) $order->subtotal, 2) }}</td>
                                </tr>
                                @if((float) $order->shipping_fee > 0)
                                    <tr>
                                        <td style="padding:6px 0;font-size:13px;color:#6B6B6B;">Shipping</td>
                                        <td align="right" style="padding:6px 0;font-size:13px;font-weight:600;color:#0A0A0A;">{{ $currencySymbol }}{{ number_format((float) $order->shipping_fee, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="padding:6px 0;font-size:13px;color:#6B6B6B;">Shipping</td>
                                        <td align="right" style="padding:6px 0;font-size:13px;font-weight:600;color:#22C55E;">Free</td>
                                    </tr>
                                @endif
                                @if((float) $order->discount > 0)
                                    <tr>
                                        <td style="padding:6px 0;font-size:13px;color:#6B6B6B;">Discount</td>
                                        <td align="right" style="padding:6px 0;font-size:13px;font-weight:600;color:#D4A843;">−{{ $currencySymbol }}{{ number_format((float) $order->discount, 2) }}</td>
                                    </tr>
                                @endif
                                @if($gst['enabled'])
                                    @if($gst['cgst'] > 0)
                                        <tr>
                                            <td style="padding:4px 0;font-size:12px;color:#6B6B6B;">CGST</td>
                                            <td align="right" style="padding:4px 0;font-size:12px;color:#444;">{{ $currencySymbol }}{{ number_format($gst['cgst'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($gst['sgst'] > 0)
                                        <tr>
                                            <td style="padding:4px 0;font-size:12px;color:#6B6B6B;">SGST</td>
                                            <td align="right" style="padding:4px 0;font-size:12px;color:#444;">{{ $currencySymbol }}{{ number_format($gst['sgst'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($gst['igst'] > 0)
                                        <tr>
                                            <td style="padding:4px 0;font-size:12px;color:#6B6B6B;">IGST</td>
                                            <td align="right" style="padding:4px 0;font-size:12px;color:#444;">{{ $currencySymbol }}{{ number_format($gst['igst'], 2) }}</td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <td colspan="2" style="padding:8px 0 0;border-top:2px solid rgba(10,10,10,.1);"></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0 0;font-size:16px;font-weight:700;color:#0A0A0A;">Total (INR)</td>
                                    <td align="right" style="padding:10px 0 0;font-size:18px;font-weight:700;color:#0A0A0A;">{{ $currencySymbol }}{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Delivery Address --}}
                    <tr>
                        <td style="padding:20px 32px;background:#FAFAF5;border-top:1px solid rgba(10,10,10,.06);">
                            <p style="margin:0 0 4px;font-size:10px;font-weight:700;letter-spacing:2px;color:#6B6B6B;text-transform:uppercase;">Delivery address</p>
                            <p style="margin:0;font-size:13px;color:#444;line-height:1.6;">
                                <strong>{{ $order->shipping_address['name'] ?? '' }}</strong><br>
                                {{ $order->shipping_address['line1'] ?? '' }}
                                @if(!empty($order->shipping_address['line2'])), {{ $order->shipping_address['line2'] }}@endif<br>
                                {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}<br>
                                Phone: {{ $order->shipping_address['phone'] ?? '—' }}
                            </p>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td align="center" style="padding:24px 32px;">
                            <a href="{{ $orderUrl }}" style="display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:12px 28px;border-radius:999px;font-size:13px;font-weight:700;">Track your order</a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid rgba(10,10,10,.06);text-align:center;">
                            <p style="margin:0;font-size:11px;color:#6B6B6B;">Thank you for shopping at Rhythm Exports. Questions? Reply to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
