@props(['product', 'dark' => false])

@php
    $price = number_format((float) str_replace(',', '', $product['price']));
    $old = $product['old_price'] ?? $product['compare_at'] ?? null;
    $old = $old !== null ? number_format((float) str_replace(',', '', $old)) : null;
    $badgeVariant = match (Str::lower(trim($product['badge'] ?? ''))) {
        'new' => 'new',
        'best seller', 'bestseller' => 'best-seller',
        'limited', 'limited edition' => 'limited',
        default => 'brand',
    };
@endphp

<article {{ $attributes->class(['product-card ui-card ui-card--interactive group flex h-full flex-col overflow-hidden', 'ui-card--dark text-white' => $dark, 'text-rythme-black' => !$dark]) }}>
    <div @class(['ui-media ui-media--product ui-media--contain relative', 'ui-media--dark' => $dark])>
        {{-- Image: Bajaao real product photo (project rule: product images from Bajaao) --}}
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }} — real product photo from Bajaao" width="1024" height="1024" class="h-full w-full object-contain p-6 transition duration-700 group-hover:scale-105" loading="lazy" decoding="async">
        @if(isset($product['badge']))
            <x-ui.badge :variant="$badgeVariant" class="absolute left-4 top-4">{{ $product['badge'] }}</x-ui.badge>
        @endif
        <button type="button" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-rythme-black shadow-sm transition hover:bg-black hover:text-white" aria-label="Add {{ $product['name'] }} to wishlist">♡</button>
        <a href="/product/{{ Str::slug($product['name']) }}" class="absolute inset-x-4 bottom-4 translate-y-16 rounded-full bg-rythme-black py-3 text-center text-xs font-bold uppercase tracking-wider text-white transition duration-300 group-hover:translate-y-0 hover:bg-gold hover:text-white">Quick view</a>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] {{ $dark ? 'text-gold' : 'text-rythme-warm-gray' }}">{{ $product['brand'] }}</p>
        <h3 class="mt-2 min-h-12 font-semibold leading-6">{{ $product['name'] }}</h3>
        <div class="mt-2 flex items-center gap-2 text-xs">
            <span class="tracking-wider text-gold">★★★★★</span><span class="{{ $dark ? 'text-white/40' : 'text-rythme-warm-gray' }}">({{ $product['reviews'] ?? 0 }})</span>
        </div>
        <div class="mt-auto flex items-end gap-2 pt-5">
            <span class="text-lg font-bold">₹{{ $price }}</span>
            @if($old)<span class="text-xs text-rythme-warm-gray line-through">₹{{ $old }}</span>@endif
        </div>
    </div>
</article>
