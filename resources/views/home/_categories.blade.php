@php
    $sec = $homeSections['categories'] ?? null;
    $cats = $homepage['popularCategories'] ?? collect();
@endphp

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
                                <img src="{{ asset('images/categories/' . $cat['slug'] . '.jpg') }}"
                                     alt="{{ $cat['name'] }}" width="600" height="600"
                                     loading="lazy" decoding="async">
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
