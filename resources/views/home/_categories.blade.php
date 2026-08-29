@php
    $sec = $homeSections['categories'] ?? null;
    $cats = $homepage['popularCategories'] ?? collect();
@endphp

@if($cats->isNotEmpty())
{{-- ============================================================
     POPULAR CATEGORIES — carousel of category tiles
     Centered heading · Swiper carousel (6/5/4/3/2.3 per view)
     Card: light tile with square product image, name + count.
     Side arrows on ≥768px; swipe with peek on mobile.
     ============================================================ --}}
<section id="categories" class="cat-mm" aria-label="Popular categories">
    <div class="cat-mm__inner">
        <h2 class="cat-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Popular Categories @endif
        </h2>

        <div class="cat-mm__carousel">
            <div class="cat-swiper swiper">
                <div class="swiper-wrapper">
                    @foreach($cats as $cat)
                        <a href="/shop?category={{ $cat['slug'] }}" class="swiper-slide cat-card"
                           aria-label="{{ $cat['name'] }} — {{ $cat['count'] }} {{ Str::plural('product', $cat['count']) }}">
                            <span class="cat-card__img">
                                @if($cat['image'])
                                    <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" width="600" height="600"
                                         loading="lazy" decoding="async">
                                @else
                                    <span class="pcard__img-fallback" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3"/></svg>
                                    </span>
                                @endif
                            </span>
                            <span class="cat-card__name">{{ $cat['name'] }}</span>
                            <span class="cat-card__count">{{ $cat['count'] }} {{ Str::plural('product', $cat['count']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="cat-prev cat-mm__arrow cat-mm__arrow--prev" aria-label="Previous categories">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" class="cat-next cat-mm__arrow cat-mm__arrow--next" aria-label="Next categories">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</section>
@endif
