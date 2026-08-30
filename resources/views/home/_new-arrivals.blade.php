@php
    $sec = $homeSections['new-arrivals'] ?? null;
    $products = $homepage['newArrivals'] ?? collect();
@endphp

@if($products->isNotEmpty())
{{-- ============================================================
     NEW ARRIVAL PRODUCTS — centered heading + responsive grid
     Desktop 5 cols · 1024: 4 · 640: 3 · mobile: 2
     Uses shared <x-mega-product-card> (same card reused in later
     product sections, mega-market style).
     ============================================================ --}}
<section id="new-arrivals" class="prod-mm" aria-label="New arrival products">
    <div class="prod-mm__inner">
        <h2 class="prod-mm__title">
            {{ trim(($sec?->title ?? 'New Arrival') . ' ' . ($sec?->title_accent ?? 'Products')) }}
        </h2>

        <div class="prod-mm__grid">
            @foreach($products as $product)
                <x-mega-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif
