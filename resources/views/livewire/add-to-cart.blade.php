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
    <p class="mt-1.5 text-xs text-muted">
        MRP inclusive of all taxes · <span class="font-semibold text-ink">EMI from ₹{{ number_format((int) round($price / 12)) }}/mo</span>
    </p>

    {{-- Variant selector --}}
    @if($product->variants->isNotEmpty())
        <fieldset class="mt-6">
            <legend class="mb-2.5 text-xs font-bold uppercase tracking-[0.18em] text-muted">
                Options — <span class="text-ink">{{ $variant?->name ?? 'Select' }}</span>
            </legend>
            <div class="flex flex-wrap gap-2">
                @foreach($product->variants as $v)
                    <button type="button"
                            wire:click="selectVariant({{ $v->id }})"
                            :disabled="false"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition
                            {{ $variantId === $v->id ? 'border-brand bg-brand text-white shadow-sm' : 'border-ink/15 bg-white text-ink hover:border-brand/50' }}">
                        {{ $v->name }}
                        @if($v->stock === 0)
                            <span class="ml-1 text-[10px] font-bold uppercase opacity-70">· Out</span>
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
        <p class="text-xs font-semibold {{ $stock > 0 ? 'text-emerald-600' : 'text-brand' }}">
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

    {{-- CTAs --}}
    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add"
                @if($stock === 0) disabled @endif
                class="inline-flex h-13 items-center justify-center gap-2 rounded-full bg-brand px-7 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(213,8,8,0.25)] transition hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-50"
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
