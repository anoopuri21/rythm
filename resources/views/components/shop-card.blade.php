@props(['product'])

@php
    $image = $product->heroImage();
    $discount = $product->discountPercent();
    $price = number_format((float) $product->price);
    $old = $product->compare_at_price !== null ? number_format((float) $product->compare_at_price) : null;
    $href = '/product/' . $product->slug;
@endphp

<article {{ $attributes->class(['group flex h-full flex-col rounded-2xl border border-ink/10 bg-white p-3.5 transition-all duration-300 hover:-translate-y-1 hover:border-brand/30 hover:shadow-[0_24px_50px_rgba(10,10,10,0.12)] sm:p-4']) }}>
    <div class="relative aspect-square overflow-hidden rounded-xl bg-paper-dark">
        @if($image)
            <img src="{{ $image }}" alt="{{ $product->name }}" width="800" height="800"
                 class="h-full w-full object-contain transition duration-700 group-hover:scale-105"
                 loading="lazy" decoding="async">
        @else
            {{-- Elegant placeholder until real Bajaao product shots are uploaded via admin --}}
            <div class="flex h-full w-full flex-col items-center justify-center gap-3 bg-gradient-to-br from-paper-dark via-paper to-paper-dark p-6 text-center">
                <svg class="h-12 w-12 text-brand/25 transition group-hover:scale-110 group-hover:text-brand/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3" />
                </svg>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-muted">{{ $product->brand->name ?? 'Rythme' }}</p>
            </div>
        @endif

        @if($discount > 0)
            <span class="absolute left-3 top-3 rounded-full bg-brand px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                {{ $discount }}% off
            </span>
        @endif

        <livewire:wishlist-button :product-id="$product->id" :variant="'card'"
                                  wire:key="wl-{{ $product->id }}" />

        <a href="{{ $href }}"
           class="absolute inset-x-3 bottom-3 translate-y-14 rounded-full bg-ink/90 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-white backdrop-blur transition duration-300 group-hover:translate-y-0 hover:bg-brand"
           aria-label="View {{ $product->name }}">
            View product
        </a>
    </div>

    <div class="flex flex-1 flex-col px-1 pb-1 pt-4">
        @if($product->brand)
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted">{{ $product->brand->name }}</p>
        @endif
        <h3 class="mt-1.5 text-sm font-semibold leading-snug text-ink">
            <a href="{{ $href }}" class="transition hover:text-brand">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex flex-wrap items-baseline gap-x-2 gap-y-1 pt-3.5">
            <span class="text-lg font-bold text-ink">₹{{ $price }}</span>
            @if($old)
                <span class="text-xs text-muted line-through">₹{{ $old }}</span>
            @endif
        </div>
    </div>
</article>
