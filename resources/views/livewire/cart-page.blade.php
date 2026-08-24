<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
    <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
        <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
        <span aria-hidden="true" class="text-ink/30">/</span>
        <span class="font-semibold text-ink" aria-current="page">Cart</span>
    </nav>

    <p class="section-kicker mb-4">Almost yours</p>
    <h1 class="section-title">Your cart</h1>

    @if(session('cart-error'))
        <p class="mt-6 max-w-xl rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">
            {{ session('cart-error') }}
        </p>
    @endif

    @if($items->isEmpty())
        <div class="mt-10 flex flex-col items-center rounded-3xl border border-dashed border-ink/15 bg-white px-6 py-24 text-center">
            <p class="text-6xl" aria-hidden="true">🎸</p>
            <h2 class="mt-6 font-playfair text-2xl font-bold text-ink">Your cart is empty</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted">
                Every great sound starts somewhere. Explore the collection and find your instrument.
            </p>
            <a href="{{ route('shop.index') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                Start shopping <span aria-hidden="true">→</span>
            </a>
        </div>
    @else
        <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)] lg:gap-12">
            {{-- Items --}}
            <div>
                @foreach($items as $item)
                    <div class="flex gap-4 border-b border-ink/10 py-6 sm:gap-6" wire:key="page-item-{{ $item->id }}">
                        <a href="/product/{{ $item->product->slug }}" class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl border border-ink/10 bg-white p-2 sm:h-32 sm:w-32">
                            @if($item->product->getFirstMediaUrl('gallery'))
                                <img src="{{ $item->product->getFirstMediaUrl('gallery') }}" alt="{{ $item->product->name }}" class="h-full w-full object-contain">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-paper-dark p-2 text-center text-[9px] font-bold uppercase tracking-widest text-muted">{{ $item->product->brand?->name ?? 'Rythme' }}</div>
                            @endif
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="/product/{{ $item->product->slug }}" class="line-clamp-2 text-sm font-semibold text-ink transition hover:text-brand sm:text-base">
                                        {{ $item->product->name }}
                                    </a>
                                    @if($item->variant)
                                        <p class="mt-1 text-xs text-muted">{{ $item->variant->name }} · SKU {{ $item->variant->sku }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-muted">SKU {{ $item->product->sku }}</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="remove({{ $item->id }})"
                                        class="shrink-0 rounded-full border border-ink/10 px-3 py-1.5 text-xs font-semibold text-muted transition hover:border-brand hover:text-brand"
                                        aria-label="Remove {{ $item->product->name }}">
                                    Remove
                                </button>
                            </div>
                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4">
                                <div class="flex items-center rounded-full border border-ink/15 bg-white">
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ max(1, $quantities[$item->id]['qty'] - 1) }})" class="flex h-10 w-10 items-center justify-center rounded-full text-ink transition hover:text-brand" aria-label="Decrease quantity">−</button>
                                    <span class="w-8 text-center text-sm font-bold text-ink">{{ $quantities[$item->id]['qty'] }}</span>
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ min(99, $quantities[$item->id]['qty'] + 1) }})" class="flex h-10 w-10 items-center justify-center rounded-full text-ink transition hover:text-brand" aria-label="Increase quantity">+</button>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    @if($item->product->compare_at_price > $item->unit_price)
                                        <span class="text-xs text-muted line-through">₹{{ number_format((float) $item->product->compare_at_price * $item->qty) }}</span>
                                    @endif
                                    <span class="text-lg font-bold text-ink">₹{{ number_format((float) $item->unit_price * $item->qty) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('shop.index') }}" class="text-link text-sm">
                        <span aria-hidden="true">←</span> Continue shopping
                    </a>
                    <button type="button" wire:click="clear" wire:confirm="Clear your whole cart?"
                            class="text-sm font-semibold text-muted transition hover:text-brand">
                        Clear cart
                    </button>
                </div>
            </div>

            {{-- Price details (Flipkart-style sticky) --}}
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-7">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-muted">Price details</h2>
                    <dl class="mt-5 space-y-3.5 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-ink/70">Subtotal ({{ $totals['count'] }} items)</dt>
                            <dd class="font-semibold text-ink">₹{{ number_format($totals['subtotal']) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ink/70">Shipping</dt>
                            <dd class="font-semibold text-emerald-600">FREE</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-ink/10 pt-3.5">
                            <dt class="font-bold text-ink">Total</dt>
                            <dd class="text-2xl font-bold text-ink">₹{{ number_format($totals['subtotal']) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        You are saving on shipping today
                    </p>

                    <a href="{{ route('checkout.index') }}"
                       class="mt-6 block w-full rounded-full bg-brand py-4 text-center text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                        Proceed to checkout
                    </a>

                    <div class="mt-6 grid grid-cols-3 gap-3 border-t border-ink/10 pt-5 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span class="text-[10px] font-semibold text-muted">Secure payments</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6 0a2 2 0 11-4 0m4 0a2 2 0 104 0m-4 0h4" /></svg>
                            <span class="text-[10px] font-semibold text-muted">Fast dispatch</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <span class="text-[10px] font-semibold text-muted">1-yr warranty</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
