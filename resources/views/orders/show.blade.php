@extends('layouts.app')

@section('title', 'Order '.$order->order_number.' — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
            <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
                <span aria-hidden="true" class="text-ink/30">/</span>
                @auth
                    <a href="{{ route('account.index') }}" class="transition hover:text-brand">My Account</a>
                    <span aria-hidden="true" class="text-ink/30">/</span>
                @endauth
                <span class="font-semibold text-ink" aria-current="page">Order {{ $order->order_number }}</span>
            </nav>

            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="section-kicker mb-3">Order status</p>
                    <h1 class="section-title">Order {{ $order->order_number }}</h1>
                    <p class="mt-3 text-sm text-muted">
                        Placed {{ $order->placed_at?->format('d M Y, h:i A') }} ·
                        <span class="font-bold text-ink">₹{{ number_format((float) $order->total, 2) }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-wide
                        {{ $order->isCancelled() ? 'bg-red-100 text-red-700' : ($order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                    <p class="mt-2 text-xs text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }} payment</p>
                </div>
            </div>

            @if(session('order_success'))
                <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('order_success') }}</p>
            @endif
            @if(session('order_error'))
                <p class="mt-6 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ session('order_error') }}</p>
            @endif

            @if(auth()->check() && auth()->id() === $order->user_id && $order->status === 'pending' && in_array($order->payment_status, ['unpaid', 'failed'], true))
                <form method="POST" action="{{ route('orders.retry-payment', $order) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="rounded-full bg-brand px-6 py-2.5 text-sm font-bold text-white transition hover:bg-brand-dark">
                        Retry payment
                    </button>
                    <p class="mt-2 text-xs text-muted">A maximum of three payment attempts is allowed. Completed payments cannot be retried.</p>
                </form>
            @endif

            {{-- Cancel (owner, pending/confirmed) --}}
            @if(auth()->check() && auth()->id() === $order->user_id && in_array($order->status, ['pending', 'confirmed'], true))
                <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-6" x-data="{ confirm: false }">
                    @csrf
                    <button type="submit" x-show="!confirm" @click.prevent="confirm = true"
                            class="rounded-full border border-brand/30 px-6 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand/5">
                        Cancel order
                    </button>
                    <span x-show="confirm" x-cloak class="inline-flex items-center gap-3 rounded-full bg-brand/10 px-5 py-2.5 text-sm">
                        <span class="font-semibold text-brand">Confirm cancellation? Paid orders create a pending refund request.</span>
                        <button type="submit" class="rounded-full bg-brand px-4 py-1.5 text-xs font-bold text-white">Yes, cancel</button>
                        <button type="button" @click="confirm = false" class="text-xs font-semibold text-muted">Keep order</button>
                    </span>
                </form>
            @endif

            {{-- ===== TRACKING TIMELINE ===== --}}
            <section aria-label="Order tracking" class="mt-10 rounded-3xl border border-ink/10 bg-white p-6 sm:p-10">
                <h2 class="font-playfair text-xl font-bold text-ink">Tracking</h2>
                <ol class="mt-8 space-y-0">
                    @foreach($timeline as $index => $step)
                        <li class="relative flex gap-5 pb-10 last:pb-0">
                            {{-- Connector --}}
                            @if(!$loop->last)
                                <span class="absolute left-[15px] top-9 h-full w-0.5 {{ $timeline[$index + 1]['done'] ? 'bg-brand' : 'bg-ink/10' }}" aria-hidden="true"></span>
                            @endif
                            {{-- Dot --}}
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2
                                {{ $step['done'] ? 'border-brand bg-brand text-white' : 'border-ink/15 bg-white text-muted' }}"
                                aria-hidden="true">
                                @if($step['done'])
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-ink/20"></span>
                                @endif
                            </span>
                            <div class="pt-1">
                                <p class="text-sm font-bold {{ $step['done'] ? 'text-ink' : 'text-muted' }}">{{ $step['label'] }}</p>
                                <p class="mt-0.5 text-xs text-muted">{{ $step['desc'] }}</p>
                                @if($step['at'])
                                    <p class="mt-1 text-[11px] font-semibold text-brand">{{ $step['at']->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <div class="mt-8 grid min-w-0 gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                {{-- Items --}}
                <section aria-label="Order items" class="min-w-0 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                    <h2 class="font-playfair text-xl font-bold text-ink">Items</h2>
                    <ul class="mt-5 divide-y divide-ink/5">
                        @foreach($order->items as $item)
                            <li class="flex items-center justify-between gap-4 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $item->name }}</p>
                                    <p class="text-xs text-muted">
                                        {{ $item->sku }} @if(!empty($item->options)) · {{ $item->options['finish'] ?? '' }} @endif · Qty {{ $item->qty }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-ink">₹{{ number_format((float) $item->total, 2) }}</p>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-4 space-y-2.5 border-t border-ink/10 pt-5 text-sm">
                        <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted">Shipping</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->shipping_fee, 2) }}</dd></div>
                        @if((float) $order->discount > 0)
                            <div class="flex justify-between"><dt class="text-muted">Discount</dt><dd class="font-semibold text-brand">−₹{{ number_format((float) $order->discount, 2) }}</dd></div>
                        @endif
                        @if((float) $order->tax > 0)
                            <div class="flex justify-between"><dt class="text-muted">Tax</dt><dd class="font-semibold text-ink">₹{{ number_format((float) $order->tax, 2) }}</dd></div>
                        @endif
                        <div class="flex justify-between border-t border-ink/10 pt-3"><dt class="font-bold text-ink">Total</dt><dd class="text-xl font-bold text-ink">₹{{ number_format((float) $order->total, 2) }}</dd></div>
                    </dl>
                </section>

                {{-- Address + payment --}}
                <aside class="min-w-0 space-y-6">
                    <section aria-label="Delivery address" class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                        <h2 class="font-playfair text-lg font-bold text-ink">Delivering to</h2>
                        <p class="mt-4 text-sm leading-6 text-ink/80">
                            <span class="font-bold">{{ $order->shipping_address['name'] ?? '' }}</span><br>
                            {{ $order->shipping_address['line1'] ?? '' }}{{ !empty($order->shipping_address['line2']) ? ', '.$order->shipping_address['line2'] : '' }}<br>
                            {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} — {{ $order->shipping_address['pincode'] ?? '' }}<br>
                            📞 {{ $order->shipping_address['phone'] ?? '' }}
                        </p>
                    </section>

                    <section aria-label="Payment" class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                        <h2 class="font-playfair text-lg font-bold text-ink">Payment</h2>
                        <p class="mt-4 text-sm text-ink/80">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }} ·
                            @if($order->payments->isNotEmpty())
                                <span class="text-xs text-muted">{{ $order->payments->last()->gateway }} · {{ $order->payments->last()->gateway_payment_id }}</span>
                            @endif
                        </p>
                    </section>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('orders.invoice', ['order' => $order]) }}" class="inline-flex items-center gap-2 rounded-full border border-ink/15 px-6 py-2.5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" /></svg>
                            Download invoice
                        </a>
                        <a href="{{ route('shop.index') }}" class="text-link text-sm">Continue shopping</a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
