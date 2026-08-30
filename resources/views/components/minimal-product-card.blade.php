@props(['product'])

@php
    // Supports BOTH: config-array products AND Eloquent Product models.
    $isModel = is_object($product) && method_exists($product, 'getFirstMediaUrl');
    $image = $isModel
        ? ($product->thumbnailImage() ?: '')
        : ($product['image'] ?? '');
    $name = $isModel ? $product->name : $product['name'];
    $brand = $isModel ? ($product->brand?->name ?? '') : ($product['brand'] ?? '');
    $price = $isModel ? (float) $product->price : (float) ($product['price'] ?? 0);
    $oldCandidate = $isModel
        ? ($product->compare_at_price ? (float) $product->compare_at_price : null)
        : (isset($product['compare_at']) ? (float) $product['compare_at'] : (isset($product['old_price']) ? (float) $product['old_price'] : null));
    $old = $oldCandidate !== null && $oldCandidate > $price ? $oldCandidate : null;
    $badge = $isModel
        ? ($product->is_featured ? 'Best Seller' : null)
        : ($product['badge'] ?? null);
    $href = '/product/' . ($isModel ? $product->slug : Str::slug($name));
@endphp

<article {{ $attributes->class(['mcard group flex h-full flex-col']) }}>
    <a href="{{ $href }}" class="flex h-full flex-col" aria-label="View {{ $name }}">
        {{-- Image — 1:1, object-contain (kabhi cut nahi), no heavy shadows --}}
        <div class="mcard__img">
            @if($image)
                <img src="{{ $image }}" alt="{{ $name }}"
                     width="480" height="480" class="mcard__img-el" loading="lazy" decoding="async">
            @else
                <span class="mcard__img-fallback" aria-hidden="true">Product image unavailable</span>
            @endif
            @if($badge)
                @php
                    $badgeVariant = match (Str::lower(trim($badge))) {
                        'new' => 'new',
                        'best seller', 'bestseller' => 'best-seller',
                        'limited', 'limited edition' => 'limited',
                        default => 'brand',
                    };
                @endphp
                <x-ui.badge :variant="$badgeVariant" class="mcard__badge">{{ $badge }}</x-ui.badge>
            @endif
        </div>
        {{-- Body — category → name → price (+ stock hint) --}}
        <div class="mcard__body flex flex-1 flex-col">
            <p class="mcard__brand">{{ $brand }}</p>
            <h3 class="mcard__name">{{ $name }}</h3>
            <div class="mcard__price mt-auto">
                <span class="mcard__price-now">₹{{ number_format($price) }}</span>
                @if($old)
                    <span class="mcard__price-old">₹{{ number_format($old) }}</span>
                @endif
                <span class="mcard__stock">In stock</span>
            </div>
        </div>
    </a>
</article>
