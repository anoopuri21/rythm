@php
    $sec = $homeSections['trending'] ?? null;
    $products = $homepage['trending'] ?? collect();
@endphp

@if($products->isNotEmpty())
<section id="trending-products" class="prod-mm" aria-label="Trending products">
    <div class="prod-mm__inner">
        <h2 class="prod-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Trending Products @endif
        </h2>

        <div class="prod-mm__grid">
            @foreach($products as $product)
                <x-mega-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif
