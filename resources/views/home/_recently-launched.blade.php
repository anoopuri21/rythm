@php
    $sec = $homeSections['bestsellers'] ?? null;
    $products = $homepage['recentlyLaunched'] ?? collect();
    $rootCategories = ($homepage['popularCategories'] ?? collect())
        ->whereIn('slug', ['guitars', 'keyboards-pianos', 'drums-percussion', 'pro-audio', 'dj-stage', 'accessories'])
        ->values();
@endphp

@if($products->isNotEmpty())
{{-- ============================================================
     RECENTLY LAUNCHED — tall banner + category links + products
     Reference layout: [banner][link list][2x3 product cards]
     ============================================================ --}}
<section class="launch-mm" aria-label="Recently launched">
    <div class="launch-mm__inner">
        <h2 class="launch-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Recently Launched @endif
        </h2>

        <div class="launch-mm__layout">
            {{-- Tall promo banner — built-in default lives in HeroBannerService::DEFAULTS;
                 an admin row in "Hero banners" (slot: launch-banner) overrides it. --}}
            @php $launch = app(\App\Services\HeroBannerService::class)->get(\App\Services\HeroBannerService::SLOT_LAUNCH_BANNER); @endphp
            <a href="{{ $launch['href'] }}" class="launch-mm__banner" style="background-image:url('{{ $launch['image'] }}')">
                <span class="launch-mm__scrim" aria-hidden="true"></span>
                <span class="launch-mm__banner-content">
                    <span class="launch-mm__kicker">{{ $launch['subtitle'] }}</span>
                    <span class="launch-mm__banner-title">{!! nl2br(e($launch['title'])) !!}</span>
                    <span class="launch-mm__cta">{{ $launch['cta_label'] }} <span aria-hidden="true">&rarr;</span></span>
                </span>
            </a>

            {{-- Category quick links with live counts --}}
            <nav class="launch-mm__list" aria-label="Browse categories">
                @foreach($rootCategories as $category)
                    <a href="/shop?category={{ $category['slug'] }}">
                        <span>{{ $category['name'] }}</span>
                        <b>{{ $category['count'] }}</b>
                    </a>
                @endforeach
                <a href="/shop" class="launch-mm__all">View all products <span aria-hidden="true">&rarr;</span></a>
            </nav>

            {{-- Product cards --}}
            <div class="launch-mm__grid">
                @foreach($products as $product)
                    <x-mega-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
