<div x-data @keydown.escape.window="if ($wire.open) $wire.close()"
     x-effect="document.body.classList.toggle('overflow-hidden', $wire.open)">

    {{-- Backdrop --}}
    <div x-cloak x-show="$wire.open" x-transition.opacity.duration.200ms
         class="fixed inset-0 z-[80] bg-black/55 backdrop-blur-sm"
         @click="$wire.close()" aria-hidden="true"></div>

    {{-- Panel --}}
    <div x-cloak x-show="$wire.open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         role="dialog" aria-modal="true" aria-label="Shopping cart"
         class="fixed inset-y-0 right-0 z-[999] flex w-[92%] max-w-md flex-col bg-paper shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-ink/10 px-6 py-5">
            <h2 class="flex items-center gap-2.5 font-playfair text-lg font-bold text-ink">
                Your Cart
                <span class="rounded-full bg-brand/10 px-2.5 py-0.5 text-xs font-bold text-brand">{{ $totals['count'] }}</span>
            </h2>
            <button type="button" @click="$wire.close()"
                    class="rounded-full p-2 text-ink transition hover:bg-ink/5" aria-label="Close cart">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        @if(session('cart-error'))
            <p class="mx-6 mt-4 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">
                {{ session('cart-error') }}
            </p>
        @endif

        {{-- Items --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            @forelse($items as $item)
                @php
                    // Check if this item is now out of stock
                    $isOutOfStock = false;
                    if ($item->product_variant_id !== null && $item->variant) {
                        $isOutOfStock = $item->variant->stock <= 0 || !$item->variant->is_active;
                    } elseif ($item->product) {
                        $isOutOfStock = $item->product->stock <= 0 || !$item->product->is_active;
                    }
                @endphp

                @if(!$isOutOfStock)
                <div class="flex gap-4 border-b border-ink/5 py-5 first:pt-0" wire:key="drawer-item-{{ $item->id }}">
                    <a href="/product/{{ $item->product->slug }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-ink/10 bg-white p-2">
                        @if($item->product->thumbnailImage())
                            <img src="{{ $item->product->thumbnailImage() }}" alt="{{ $item->product->name }}" width="480" height="480" class="h-full w-full object-contain" loading="lazy" decoding="async">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-paper-dark text-[9px] font-bold uppercase tracking-widest text-muted">{{ $item->product->brand?->name ?? 'Rythme' }}</div>
                        @endif
                    </a>
                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <a href="/product/{{ $item->product->slug }}" class="truncate text-sm font-semibold text-ink hover:text-brand">
                                {{ $item->product->name }}
                            </a>
                            <button type="button" wire:click="remove({{ $item->id }})" class="shrink-0 text-muted transition hover:text-brand" aria-label="Remove {{ $item->product->name }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        @if($item->variant)
                            <p class="mt-0.5 text-xs text-muted">{{ $item->variant->name }} · {{ $item->variant->sku }}</p>
                        @endif
                        <div class="mt-auto flex items-center justify-between pt-2">
                            <div class="flex items-center rounded-full border border-ink/15 bg-white">
                                <button type="button" wire:click="updateQty({{ $item->id }}, {{ max(1, $quantities[$item->id]['qty'] - 1) }})" class="flex h-8 w-8 items-center justify-center rounded-full text-ink transition hover:text-brand" aria-label="Decrease quantity">−</button>
                                <span class="w-6 text-center text-xs font-bold text-ink">{{ $quantities[$item->id]['qty'] }}</span>
                                <button type="button" wire:click="updateQty({{ $item->id }}, {{ min(99, $quantities[$item->id]['qty'] + 1) }})" class="flex h-8 w-8 items-center justify-center rounded-full text-ink transition hover:text-brand" aria-label="Increase quantity">+</button>
                            </div>
                            <p class="text-sm font-bold text-ink">₹{{ number_format((float) $item->unit_price * $item->qty) }}</p>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div class="flex flex-col items-center py-16 text-center">
                    <p class="text-5xl" aria-hidden="true">🛒</p>
                    <h3 class="mt-5 font-playfair text-xl font-bold text-ink">Your cart is empty</h3>
                    <p class="mt-2 max-w-[240px] text-sm text-muted">Fill it with something that makes a sound you love.</p>
                    <a href="/shop" @click="$wire.close()" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                        Browse the shop
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Footer / checkout --}}
        @if($items->isNotEmpty())
            <div class="ui-summary-panel mx-4 mb-4 rounded-2xl px-5 py-5">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm text-muted">Subtotal</span>
                    <span class="text-xl font-bold text-ink">₹{{ number_format($totals['subtotal']) }}</span>
                </div>
                <p class="mb-4 text-xs text-muted">Shipping and taxes calculated at checkout.</p>
                <a href="{{ route('cart.index') }}" class="mb-2.5 block w-full rounded-full bg-ink py-3.5 text-center text-sm font-bold text-white transition hover:bg-ink-soft">
                    View full cart
                </a>
                <a href="{{ route('checkout.index') }}" class="block w-full rounded-full bg-brand py-3.5 text-center text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Proceed to checkout
                </a>
            </div>
        @endif
    </div>
</div>
