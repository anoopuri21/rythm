@php
    $sec = $homeSections['new-arrivals'] ?? null;
    $products = $homepage['newArrivals'] ?? collect();
@endphp

{{-- ============================================================
     NEW ARRIVAL PRODUCTS — centered heading + responsive grid
     Desktop 5 cols · 1024: 4 · 640: 3 · mobile: 2
     Uses shared <x-mega-product-card> (same card reused in later
     product sections, mega-market style).
     ============================================================ --}}
<section id="new-arrivals" class="prod-mm" aria-label="New arrival products">
    <div class="prod-mm__inner">
        <h2 class="prod-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else New Arrival Products @endif
        </h2>

        <div class="prod-mm__grid">
            @foreach($products as $product)
                <x-mega-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
