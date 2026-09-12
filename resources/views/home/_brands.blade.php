@php
    $sec = $homeSections['brands'] ?? null;
    $brands = $homepage['brands'] ?? collect();
    if ($brands->isEmpty() && isset($homepage['brandNames'])) {
        // Backward-compatible fallback if only names are present.
        $brands = collect($homepage['brandNames'])->map(fn ($name) => [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug((string) $name),
            'count' => 0,
            'logo' => null,
        ]);
    }
@endphp

@if($brands->isNotEmpty())
{{-- ============================================================
     POPULAR BRANDS — horizontal slide carousel
     Logo tiles when available; monogram wordmark otherwise.
     Side arrows ≥768px; swipe + peek on mobile; autoplay desktop.
     ============================================================ --}}
<section class="brand-mm" aria-label="Popular brands">
    <div class="brand-mm__inner">
        <div class="brand-mm__header">
            <p class="brand-mm__kicker">
                {{ $sec?->kicker ?: 'Trusted makers' }}
            </p>
            <h2 class="brand-mm__title">
                {{ trim(($sec?->title ?? 'Popular') . ' ' . ($sec?->title_accent ?? 'Brands')) }}
            </h2>
            @if($sec?->content)
                <div class="brand-mm__sub">{!! strip_tags($sec->content, '<p><br><strong><em>') !!}</div>
            @else
                <p class="brand-mm__sub">Swipe through the names behind the music — shop any brand in one tap.</p>
            @endif
        </div>

        <div class="brand-mm__carousel">
            <div class="brand-swiper swiper">
                <div class="swiper-wrapper">
                    @foreach($brands as $brand)
                        @php
                            $name = is_array($brand) ? ($brand['name'] ?? '') : (string) $brand;
                            $slug = is_array($brand) ? ($brand['slug'] ?? \Illuminate\Support\Str::slug($name)) : \Illuminate\Support\Str::slug($name);
                            $logo = is_array($brand) ? ($brand['logo'] ?? null) : null;
                            $count = is_array($brand) ? (int) ($brand['count'] ?? 0) : 0;
                            $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
                                ->filter()
                                ->take(2)
                                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                                ->implode('');
                            if ($initials === '') {
                                $initials = mb_strtoupper(mb_substr($name, 0, 2));
                            }
                        @endphp
                        <a href="/shop?brand%5B%5D={{ urlencode($slug) }}"
                           class="swiper-slide brand-card"
                           aria-label="Shop {{ $name }}@if($count > 0) — {{ $count }} {{ \Illuminate\Support\Str::plural('product', $count) }}@endif">
                            <span class="brand-card__logo" aria-hidden="true">
                                @if($logo)
                                    <img src="{{ $logo }}" alt="" width="160" height="80" loading="lazy" decoding="async">
                                @else
                                    <span class="brand-card__monogram">{{ $initials }}</span>
                                @endif
                            </span>
                            <span class="brand-card__name">{{ $name }}</span>
                            @if($count > 0)
                                <span class="brand-card__count">{{ $count }} {{ \Illuminate\Support\Str::plural('product', $count) }}</span>
                            @else
                                <span class="brand-card__count">Shop collection</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="brand-prev brand-mm__arrow brand-mm__arrow--prev" aria-label="Previous brands">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" class="brand-next brand-mm__arrow brand-mm__arrow--next" aria-label="Next brands">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="brand-mm__pagination" aria-hidden="true"></div>
        </div>
    </div>
</section>
@endif
