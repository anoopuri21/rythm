@props(['product'])

@php
    $price = number_format((float) $product['price']);
    $old = $product['compare_at'] ?? $product['old_price'] ?? null;
    $old = $old !== null ? number_format((float) $old) : null;
    $badge = $product['badge'] ?? null;
    $href = '/product/' . Str::slug($product['name']);
@endphp

<article {{ $attributes->class(['mcard group flex h-full flex-col']) }}>
    <a href="{{ $href }}" class="flex h-full flex-col" aria-label="View {{ $product['name'] }}">
        {{-- Image — 1:1, object-contain (kabhi cut nahi), no heavy shadows --}}
        <div class="mcard__img">
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }} — real product photo from Bajaao"
                 width="800" height="800" class="mcard__img-el" loading="lazy" decoding="async">
            @if($badge)
                <span class="mcard__badge">{{ $badge }}</span>
            @endif
        </div>
        {{-- Body — category → name → price (+ stock hint) --}}
        <div class="mcard__body flex flex-1 flex-col">
            <p class="mcard__brand">{{ $product['brand'] }}</p>
            <h3 class="mcard__name">{{ $product['name'] }}</h3>
            <div class="mcard__price mt-auto">
                <span class="mcard__price-now">₹{{ $price }}</span>
                @if($old)
                    <span class="mcard__price-old">₹{{ $old }}</span>
                @endif
                <span class="mcard__stock">In stock</span>
            </div>
        </div>
    </a>
</article>
