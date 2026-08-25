@props(['product'])

@php
    $image = $product->heroImage();
    $onSale = $product->compare_at_price !== null && (float) $product->compare_at_price > (float) $product->price;
    $rating = $product->reviews_avg_rating ?? null;
    $href = route('product.show', $product->slug);
@endphp

{{-- ============================================================
     MEGA PRODUCT CARD — shared card for homepage product grids
     Image tile (zoom on hover) · sale badge · wishlist overlay ·
     hover "view product" bar · category · name · stars · price
     ============================================================ --}}
<article class="pcard">
    <div class="pcard__media">
        @if($onSale)
            <span class="pcard__badge">Sale!</span>
        @endif

        <a href="{{ $href }}" class="pcard__img" aria-label="{{ $product->name }}" tabindex="-1">
            @if($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" width="600" height="600" loading="lazy" decoding="async">
            @else
                <span class="pcard__img-fallback" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3"/></svg>
                </span>
            @endif
        </a>

        {{-- Wishlist toggle (existing Livewire component, card overlay variant) --}}
        <livewire:wishlist-button :product-id="$product->id" :variant="'card'" wire:key="pcard-wl-{{ $product->id }}" />

        {{-- Hover reveal: view product bar --}}
        <a href="{{ $href }}" class="pcard__view">View product</a>
    </div>

    <div class="pcard__body">
        @if($product->category)
            <a href="/shop?category={{ $product->category->slug }}" class="pcard__cat">{{ $product->category->name }}</a>
        @endif
        <h3 class="pcard__name">
            <a href="{{ $href }}">{{ $product->name }}</a>
        </h3>

        @if($rating)
            <p class="pcard__stars" aria-label="Rated {{ number_format((float) $rating, 1) }} out of 5">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="{{ $i <= round($rating) ? 'is-on' : '' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.95 5.98 6.6.96-4.78 4.65 1.13 6.58L12 17.57l-5.9 3.1 1.13-6.58L2.45 9.44l6.6-.96L12 2.5z"/></svg>
                @endfor
            </p>
        @endif

        <p class="pcard__price">
            @if($onSale)
                <del>₹{{ number_format((float) $product->compare_at_price) }}</del>
            @endif
            <ins>₹{{ number_format((float) $product->price) }}</ins>
        </p>
    </div>
</article>
