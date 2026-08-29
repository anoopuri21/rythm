@php
    $brand = config('rythme.brand_name');
    // HERO SLIDES — admin-driven (hero_slides table + desktop/mobile media collections).
    $slides = ($homepage['heroSlides'] ?? collect())->take(3);
    // Fallback imagery when a slide has no media attached (AI-generated, local).
    $fallbackSlideImages = [
        asset('images/hero/grid-slide-guitar.jpg'),
        asset('images/hero/grid-slide-synth.jpg'),
        asset('images/hero/grid-slide-guitar.jpg'),
    ];
@endphp

{{-- ============================================================
     HERO — mega-market split grid
     Desktop (≥1024): [ slider 50% | tall banner 25% | 2 stacked 25% ]
     Tablet (768–1023): slider full width, banners in 2-col grid below
     Mobile (<768): everything stacked full width
     ============================================================ --}}
<section id="hero" class="hero-mm" aria-label="Featured collections">
    <div class="hero-mm__grid">

        {{-- ===== MAIN SLIDER (left) ===== --}}
        <div class="hero-mm__slider">
            <div class="hero-swiper swiper h-full">
                <div class="swiper-wrapper">
                    @forelse($slides as $slide)
                        <article class="swiper-slide relative overflow-hidden">
                            <picture class="absolute inset-0">
                                @if($slide->mobileImageUrl())
                                    <source media="(max-width: 767px)"
                                            srcset="{{ $slide->mobileImageUrl() }}"
                                            width="768" height="1024">
                                @endif
                                <img src="{{ $slide->desktopImageUrl() ?: $fallbackSlideImages[$loop->index % 3] }}"
                                     alt="" width="1200" height="896"
                                     class="hero-slide-image absolute inset-0 h-full w-full object-cover"
                                     loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                     fetchpriority="{{ $loop->first ? 'high' : 'low' }}" decoding="async">
                            </picture>
                            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-transparent"></div>

                            <div class="hero-copy relative z-10 flex h-full flex-col justify-center px-7 py-10 text-white sm:px-10 lg:px-12">
                                <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/60">{{ $slide->eyebrow }}</p>
                                @if($loop->first)
                                    <h1 class="max-w-md text-3xl font-extrabold leading-[1.05] tracking-tight sm:text-4xl lg:text-[2.75rem]">
                                        {{ $slide->title }} <span class="text-white/70">{{ $slide->accent }}</span>
                                    </h1>
                                @else
                                    <h2 class="max-w-md text-3xl font-extrabold leading-[1.05] tracking-tight sm:text-4xl lg:text-[2.75rem]">
                                        {{ $slide->title }} <span class="text-white/70">{{ $slide->accent }}</span>
                                    </h2>
                                @endif
                                <p class="mt-4 max-w-sm text-sm leading-6 text-white/70 line-clamp-2">{{ $slide->copy }}</p>
                                <div class="mt-7">
                                    <a href="{{ str_starts_with((string) $slide->cta_href, '/') && ! str_starts_with((string) $slide->cta_href, '//') ? $slide->cta_href : '/shop' }}" class="hero-mm__cta">{{ $slide->cta_label ?: 'View details' }}</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="swiper-slide relative overflow-hidden">
                            <img src="{{ $fallbackSlideImages[0] }}" alt="" width="1200" height="896"
                                 class="hero-slide-image absolute inset-0 h-full w-full object-cover"
                                 fetchpriority="high" decoding="async">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-transparent"></div>
                            <div class="hero-copy relative z-10 flex h-full flex-col justify-center px-7 py-10 text-white sm:px-10 lg:px-12">
                                <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/60">Rhythm Exports</p>
                                <h1 class="max-w-md text-3xl font-extrabold leading-[1.05] tracking-tight sm:text-4xl lg:text-[2.75rem]">
                                    Find your instrument. <span class="text-white/70">Shape your sound.</span>
                                </h1>
                                <p class="mt-4 max-w-sm text-sm leading-6 text-white/70">Explore guitars, keyboards, drums and studio gear for every stage of your musical journey.</p>
                                <div class="mt-7"><a href="{{ route('shop.index') }}" class="hero-mm__cta">Explore instruments</a></div>
                            </div>
                        </article>
                    @endforelse
                </div>
                <div class="hero-pagination swiper-pagination !bottom-5 !text-left"></div>
                <button type="button" class="hero-pause hero-mm__pause" aria-pressed="false" aria-label="Pause featured collections">
                    <span data-hero-pause-label>Pause</span>
                </button>
                <button type="button" class="hero-prev hero-mm__navbtn hero-mm__navbtn--prev" aria-label="Previous slide">←</button>
                <button type="button" class="hero-next hero-mm__navbtn hero-mm__navbtn--next" aria-label="Next slide">→</button>
            </div>
        </div>

        {{-- ===== TALL BANNER (middle) ===== --}}
        <a href="/shop?category=keyboards-pianos" class="hero-mm__banner hero-mm__banner--tall">
            <img src="{{ asset('images/hero/grid-banner-piano.jpg') }}" alt="Digital stage piano" width="896" height="1200" loading="eager" decoding="async">
            <span class="hero-mm__banner-scrim" aria-hidden="true"></span>
            <span class="hero-mm__banner-copy">
                <span class="hero-mm__banner-title">Stage Pianos</span>
                <span class="hero-mm__banner-sub">As expressive as it is portable</span>
                <span class="hero-mm__banner-link">Shop now</span>
            </span>
        </a>

        {{-- ===== SMALL BANNER 1 (top right) ===== --}}
        <a href="/shop?category=drums-percussion" class="hero-mm__banner hero-mm__banner--small hero-mm__banner--s1">
            <img src="{{ asset('images/hero/grid-banner-tabla.jpg') }}" alt="Tabla set" width="1312" height="816" loading="eager" decoding="async">
            <span class="hero-mm__banner-copy">
                <span class="hero-mm__banner-title">Tabla Sets</span>
                <span class="hero-mm__banner-sub">Explore percussion instruments</span>
                <span class="hero-mm__banner-link">Shop now →</span>
            </span>
        </a>

        {{-- ===== SMALL BANNER 2 (bottom right) ===== --}}
        <a href="/shop?category=pro-audio" class="hero-mm__banner hero-mm__banner--small hero-mm__banner--s2">
            <img src="{{ asset('images/hero/grid-banner-headphones.jpg') }}" alt="Studio headphones" width="1312" height="816" loading="eager" decoding="async">
            <span class="hero-mm__banner-copy">
                <span class="hero-mm__banner-title">Studio Gear</span>
                <span class="hero-mm__banner-sub">Explore current studio offers</span>
                <span class="hero-mm__banner-link">Shop now →</span>
            </span>
        </a>
    </div>
</section>
