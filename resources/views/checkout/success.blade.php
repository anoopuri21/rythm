@extends('layouts.app')

@section('title', 'Order Confirmed — Rythme Music Store')
@section('meta_description', 'Your order has been placed successfully.')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-3xl px-5 py-16 text-center sm:px-8 sm:py-24">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <p class="section-kicker mb-4 mt-10 justify-center">Payment successful</p>
            <h1 class="section-title">Thank you! Your order is confirmed.</h1>
            <p class="mx-auto mt-6 max-w-lg text-base leading-7 text-muted">
                A confirmation email is on its way. Your instruments are being packed
                with care and will ship to your address shortly.
            </p>

            <div class="mt-10 rounded-3xl border border-ink/10 bg-white p-8 text-left">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink/10 pb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-muted">Order number</p>
                        <p class="mt-1 font-mono text-lg font-bold text-ink">{{ $order->order_number }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-4 py-1.5 text-xs font-bold uppercase tracking-wide text-emerald-700">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </div>

                <ul class="divide-y divide-ink/5">
                    @foreach($order->items as $item)
                        <li class="flex items-center justify-between gap-4 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">{{ $item->name }}</p>
                                @if(!empty($item->options))
                                    <p class="text-xs text-muted">{{ $item->options['finish'] ?? json_encode($item->options) }}</p>
                                @endif
                            </div>
                            <p class="shrink-0 text-sm text-ink">₹{{ number_format((float) $item->unit_price) }} × {{ $item->qty }}</p>
                        </li>
                    @endforeach
                </ul>

                <div class="flex items-center justify-between border-t border-ink/10 pt-5">
                    <p class="font-bold text-ink">Total paid</p>
                    <p class="text-2xl font-bold text-ink">₹{{ number_format((float) $order->total) }}</p>
                </div>

                <div class="mt-6 rounded-2xl bg-paper-dark p-5 text-sm text-muted">
                    <p class="font-bold text-ink">Delivering to</p>
                    <p class="mt-1.5 leading-6">
                        {{ $order->shipping_address['name'] ?? '' }}<br>
                        {{ $order->shipping_address['line1'] ?? '' }}{{ !empty($order->shipping_address['line2']) ? ', ' . $order->shipping_address['line2'] : '' }}<br>
                        {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}<br>
                        📞 {{ $order->shipping_address['phone'] ?? '' }}
                    </p>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full bg-brand px-8 py-3.5 text-sm font-bold text-white transition hover:bg-brand-dark">
                    Continue shopping <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('home') }}" class="text-link text-sm">Back to home</a>
            </div>
        </div>
    </div>
@endsection
