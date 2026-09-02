<div class="mt-7 rounded-3xl border border-ink/10 bg-white p-6 sm:p-7">
    {{-- Price row --}}
    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
        <span class="text-3xl font-bold tracking-tight text-ink" x-data x-init="$wire.$watch('qty', () => $wire.$refresh())">
            ₹{{ number_format($price) }}
        </span>
        @if($compareAt > 0 && $compareAt > $price)
            <span class="text-base text-muted line-through">₹{{ number_format($compareAt) }}</span>
            <span class="rounded-full bg-brand/10 px-2.5 py-1 text-xs font-bold text-brand">
                {{ $compareAt > 0 ? round((($compareAt - $price) / $compareAt) * 100) : 0 }}% off
            </span>
        @endif
    </div>
    <p class="mt-1.5 text-xs leading-5 text-muted">
        Displayed price is revalidated at checkout. Shipping, tax and available payment methods are shown before payment.
    </p>

    {{-- Variant selector --}}
    @if($variantsWithColor->isNotEmpty())
        <fieldset class="mt-6">
            <legend class="mb-2.5 text-xs font-bold uppercase tracking-[0.18em] text-muted">
                Options — <span class="text-ink">{{ $variant?->name ?? 'Select' }}</span>
            </legend>
            <div class="flex flex-wrap gap-3">
                @foreach($variantsWithColor as $v)
                    @php
                        $hasColor = !empty($v['color_hex']);
                        $isSelected = $variantId === $v['id'];
                    @endphp
                    <button type="button"
                            wire:click="selectVariant({{ $v['id'] }})"
                            class="relative rounded-full border-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand/30
                            {{ $isSelected ? 'border-brand ring-2 ring-brand/20' : 'border-ink/15 hover:border-brand/50' }}"
                            style="{{ $hasColor ? 'padding: 4px;' : '' }}"
                            title="{{ $v['name'] }}"
                            aria-label="{{ $v['name'] }}">
                        @if($hasColor)
                            {{-- Color circle for variants with color attribute --}}
                            <span class="block rounded-full border border-black/10"
                                  style="width: 36px; height: 36px; background-color: {{ $v['color_hex'] }};"></span>
                            @if($isSelected)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand text-[8px] font-bold text-white">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                            @endif
                        @else
                            {{-- Text button for variants without color --}}
                            <span class="block px-4 py-2 text-sm font-semibold {{ $isSelected ? 'text-white' : 'text-ink' }}">
                                {{ $v['name'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- Qty + stock --}}
    <div class="mt-6 flex flex-wrap items-center gap-4">
        <div class="flex items-center rounded-full border border-ink/15 bg-paper">
            <button type="button" wire:click="setQty({{ $qty - 1 }})" class="flex h-11 w-11 items-center justify-center rounded-full text-lg font-bold text-ink transition hover:text-brand" aria-label="Decrease quantity">−</button>
            <span class="w-8 text-center text-sm font-bold text-ink" wire:loading.class="opacity-50">{{ $qty }}</span>
            <button type="button" wire:click="setQty({{ $qty + 1 }})" class="flex h-11 w-11 items-center justify-center rounded-full text-lg font-bold text-ink transition hover:text-brand" aria-label="Increase quantity">+</button>
        </div>
        <p class="text-xs font-semibold {{ $stock > 0 ? 'text-emerald-700' : 'text-brand' }}">
            @if($stock > 0)
                In stock — {{ $stock }} available
            @else
                Out of stock
            @endif
        </p>
    </div>

    {{-- Error --}}
    @if($error)
        <p class="mt-4 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">
            {{ $error }}
        </p>
    @endif

    {{-- Success --}}
    @if($added)
        <p class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
            Added to your cart!
        </p>
    @endif

    @if($stock <= 0)
        <div class="mt-5 rounded-2xl border border-brand/20 bg-brand/5 p-4" aria-labelledby="stock-alert-title">
            <h3 id="stock-alert-title" class="text-sm font-bold text-ink">Want a stock update?</h3>
            @auth
                <label class="mt-3 flex items-start gap-2 text-xs leading-5 text-muted">
                    <input type="checkbox" wire:model="notifyConsent" class="mt-1 rounded border-ink/20 text-brand focus:ring-brand" />
                    <span>I agree to receive one stock-availability email for this item. No marketing messages.</span>
                </label>
                <button type="button" wire:click="requestStockNotification" wire:loading.attr="disabled" wire:target="requestStockNotification"
                        class="mt-3 inline-flex items-center rounded-full bg-ink px-4 py-2 text-xs font-bold text-white transition hover:bg-brand disabled:opacity-50">
                    Request stock email
                </button>
            @else
                <p class="mt-1 text-xs leading-5 text-muted">Log in to request a stock-availability email without sharing your address on this page.</p>
                <a href="{{ route('login') }}" class="mt-3 inline-flex text-xs font-bold text-brand underline underline-offset-4">Log in to request an update</a>
            @endauth
            @if($notifyError)
                <p class="mt-3 text-xs font-semibold text-brand" role="alert">{{ $notifyError }}</p>
            @endif
            @if($notifySuccess)
                <p class="mt-3 text-xs font-semibold text-emerald-700" role="status">Your request is recorded. We will email you if this item is restocked.</p>
            @endif
            <a href="{{ route('contact', ['product' => $product->slug]) }}" class="mt-3 inline-flex text-xs font-semibold text-brand underline underline-offset-4">
                Ask about availability
            </a>
        </div>
    @endif

    {{-- CTAs --}}
    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add"
                @if($stock <= 0) disabled @endif
                class="inline-flex h-13 items-center justify-center gap-2 rounded-full bg-brand px-7 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Add {{ $product->name }} to cart">
            <span wire:loading.remove wire:target="add" class="inline-flex items-center gap-2">
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                Add to Cart
            </span>
            <span wire:loading wire:target="add" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                Adding…
            </span>
        </button>
        <a href="{{ route('shop.index') }}"
           class="inline-flex h-13 items-center justify-center gap-2 rounded-full border border-ink/15 px-7 py-3.5 text-sm font-bold text-ink transition hover:border-brand hover:text-brand">
            Continue shopping
        </a>
    </div>
</div>
