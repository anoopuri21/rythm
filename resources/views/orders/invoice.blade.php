@extends('layouts.app')

@section('title', 'Invoice '.$order->order_number.' — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
            <div class="mb-8 flex items-center justify-between">
                <p class="section-kicker mb-0">Tax invoice</p>
                <button type="button" onclick="window.print()" class="rounded-full bg-ink px-6 py-2.5 text-sm font-bold text-white transition hover:bg-brand">
                    Print / Save PDF
                </button>
            </div>

            <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-10">
                {{-- Header --}}
                <div class="flex flex-wrap items-start justify-between gap-6 border-b border-ink/10 pb-8">
                    <div>
                        <p class="font-bebas text-3xl font-bold tracking-wide text-brand">RHYTHM EXPORTS</p>
                        <p class="mt-1 text-xs text-muted">42, Music Lane, Karol Bagh<br>New Delhi, Delhi 110005</p>
                    </div>
                    <div class="text-right text-sm">
                        <p class="font-mono font-bold text-ink">{{ $order->order_number }}</p>
                        <p class="mt-1 text-xs text-muted">Invoice date: {{ $order->placed_at?->format('d M Y') }}</p>
                        <p class="text-xs text-muted">Payment: {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</p>
                    </div>
                </div>

                {{-- Bill to --}}
                <div class="border-b border-ink/10 py-6 text-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-muted">Billed to</p>
                    <p class="mt-2 font-bold text-ink">{{ $order->shipping_address['name'] ?? '' }}</p>
                    <p class="text-ink/80">{{ $order->shipping_address['line1'] ?? '' }}{{ !empty($order->shipping_address['line2']) ? ', '.$order->shipping_address['line2'] : '' }}</p>
                    <p class="text-ink/80">{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}</p>
                    <p class="text-ink/80">📞 {{ $order->shipping_address['phone'] ?? '' }} · {{ $order->email }}</p>
                </div>

                {{-- Items --}}
                <table class="mt-6 w-full text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-3 font-bold">Item</th>
                            <th class="py-3 text-center font-bold">Qty</th>
                            <th class="py-3 text-right font-bold">Rate</th>
                            <th class="py-3 text-right font-bold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/5">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-3.5">
                                    <p class="font-semibold text-ink">{{ $item->name }}</p>
                                    <p class="text-xs text-muted">{{ $item->sku }}</p>
                                </td>
                                <td class="py-3.5 text-center text-ink/70">{{ $item->qty }}</td>
                                <td class="py-3.5 text-right text-ink/70">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-3.5 text-right font-bold text-ink">₹{{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totals --}}
                <dl class="ml-auto mt-6 w-full max-w-xs space-y-2.5 border-t border-ink/10 pt-5 text-sm">
                    <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Shipping</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->shipping_fee, 2) }}</dd></div>
                    @if((float) $order->discount > 0)
                        <div class="flex justify-between"><dt class="text-muted">Discount</dt><dd class="font-semibold text-brand">−₹{{ number_format((float) $order->discount, 2) }}</dd></div>
                    @endif
                    @if((float) $order->tax > 0)
                        <div class="flex justify-between"><dt class="text-muted">Tax</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->tax, 2) }}</dd></div>
                    @endif
                    <div class="flex justify-between border-t border-ink/10 pt-3"><dt class="font-bold text-ink">Total (INR)</dt><dd class="text-xl font-bold text-ink">₹{{ number_format((float) $order->total, 2) }}</dd></div>
                </dl>

                <p class="mt-10 border-t border-ink/10 pt-6 text-center text-[11px] text-muted">
                    Thank you for shopping at Rhythm Exports. This invoice reflects the totals recorded when the order was placed.
                </p>
            </div>
        </div>
    </div>
@endsection
