@php
    $brand = config('rythme.brand_name');
    $slides = [
        ['image' => 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747', 'eyebrow' => 'High quality · Best sellers', 'title' => 'Premium gear.', 'accent' => 'Zero compromise.', 'copy' => 'Every instrument we ship is inspected, set up and ready to perform — from beginner favourites to stage-ready pro models. Real products, real quality.', 'cta' => 'Explore instruments', 'cta_href' => '/shop'],
        ['image' => 'https://www.bajaao.com/cdn/shop/files/ROL-FP30XBK.jpg?v=1779349747', 'eyebrow' => 'High quality · Keys & pianos', 'title' => 'Play the piano.', 'accent' => 'Feel every note.', 'copy' => 'Digital pianos with weighted keys and rich, expressive sound — crafted for practice rooms and stages alike.', 'cta' => 'Shop keyboards', 'cta_href' => '/category/keyboards-pianos'],
        ['image' => 'images/hero-guitar.jpg', 'eyebrow' => 'Craft your signature sound', 'title' => 'Feel the music.', 'accent' => 'Own the sound.', 'copy' => 'Handpicked instruments, expertly set up and delivered with care anywhere in India.', 'cta' => 'Explore instruments', 'cta_href' => '/shop'],
        ['image' => 'images/hero-piano.jpg', 'eyebrow' => 'The keys to expression', 'title' => 'Every note.', 'accent' => 'Entirely yours.', 'copy' => 'From first melodies to concert stages, discover keys that move with your ambition.', 'cta' => 'Shop keyboards', 'cta_href' => '/category/keyboards-pianos'],
        ['image' => 'images/hero-studio.jpg', 'eyebrow' => 'Build your perfect studio', 'title' => 'Capture the moment.', 'accent' => 'Keep it forever.', 'copy' => 'Professional recording essentials selected for clarity, character and lasting performance.', 'cta' => 'Explore pro audio', 'cta_href' => '/category/pro-audio'],
    ];
    $heroMode = $heroMode ?? config('rythme.hero_mode', 'slider');
@endphp

@if($heroMode === 'video')
    {{-- ============ HERO MODE 2 · VIDEO BANNER (theme-matched, dark + gold) ============ --}}
    {{-- Video source: Pexels free license (CC0) — https://www.pexels.com/video/man-playing-guitar-854924/ --}}
    <section id="hero" class="relative flex h-[calc(100svh-4rem)] min-h-[560px] w-full items-center overflow-hidden bg-rythme-black lg:h-[calc(100svh-7.5rem)]" aria-label="Featured collection video">
        <video class="hero-video absolute inset-0 h-full w-full object-cover opacity-80"
               autoplay muted loop playsinline preload="metadata"
               poster="{{ asset('images/video-showcase-poster.jpg') }}">
            <source src="{{ asset(config('rythme.hero_video_url')) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-black/20"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/40"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-5 pt-28 lg:pt-36 sm:px-8 lg:px-12">
            <div class="max-w-3xl text-white">
                <p class="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.32em] text-gold-light" data-reveal="fade">
                    <span class="h-px w-10 bg-gold"></span>{{ $brand }} · Premium instruments
                </p>
                <h1 class="font-playfair text-5xl leading-[0.98] sm:text-7xl lg:text-[6.4rem]" data-reveal="up">
                    Feel the music.<br><em class="font-normal text-red-gradient">Own the sound.</em>
                </h1>
                <p class="mt-7 max-w-xl text-base leading-7 text-white/75 sm:text-lg" data-reveal="up">
                    Handpicked guitars, keyboards, drums and studio gear — set up by musicians, delivered with care across India.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-5" data-reveal="up">
                    <a href="/shop" class="inline-flex items-center gap-3 rounded-full bg-white px-7 py-4 text-sm font-bold text-rythme-black transition hover:bg-white/85">Explore instruments <span aria-hidden="true">→</span></a>
                    <a href="#categories" class="btn-ghost-light">Browse categories</a>
                </div>
            </div>
        </div>

        <a href="#categories" class="absolute bottom-8 left-8 z-20 hidden items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-white/60 lg:flex">
            <span class="flex h-10 w-6 justify-center rounded-full border border-white/30 pt-2"><span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gold"></span></span> Scroll to discover
        </a>
        <span class="pointer-events-none absolute bottom-8 right-8 z-20 hidden items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.25em] text-white/60 backdrop-blur-sm sm:flex" aria-hidden="true">
            <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold opacity-60"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-gold"></span></span> Video · {{ $brand }}
        </span>
    </section>
@else
    {{-- ============ HERO MODE 1 · CINEMATIC SLIDER ============ --}}
    <section id="hero" class="relative h-[calc(100svh-4rem)] min-h-[560px] w-full overflow-hidden bg-rythme-black lg:h-[calc(100svh-7.5rem)]" aria-label="Featured collections">
        <div class="hero-swiper swiper h-full">
            <div class="swiper-wrapper">
                @foreach($slides as $slide)
                    <article class="swiper-slide relative overflow-hidden">
                        <img src="{{ asset($slide['image']) }}" alt="" width="1376" height="768" class="hero-slide-image absolute inset-0 h-full w-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}" fetchpriority="{{ $loop->first ? 'high' : 'low' }}" decoding="async">
                        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/65 to-black/10"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/30"></div>
                        <div class="relative z-10 mx-auto flex h-full max-w-7xl items-center px-5 sm:px-8 lg:px-12">
                            <div class="hero-copy max-w-3xl text-white">
                                <p class="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.32em] text-gold-light">
                                    <span class="h-px w-10 bg-gold"></span>{{ $slide['eyebrow'] }}
                                </p>
                                @if($loop->first)
                                    <h1 class="font-playfair text-5xl leading-[0.98] sm:text-7xl lg:text-[6.4rem]">
                                        {{ $slide['title'] }}<br><em class="font-normal text-red-gradient">{{ $slide['accent'] }}</em>
                                    </h1>
                                @else
                                    <h2 class="font-playfair text-5xl leading-[0.98] sm:text-7xl lg:text-[6.4rem]">
                                        {{ $slide['title'] }}<br><em class="font-normal text-red-gradient">{{ $slide['accent'] }}</em>
                                    </h2>
                                @endif
                                <p class="mt-7 max-w-xl text-base leading-7 text-white/70 sm:text-lg">{{ $slide['copy'] }}</p>
                                <div class="mt-9 flex flex-wrap items-center gap-5">
                                    <a href="{{ $slide['cta_href'] }}" class="inline-flex items-center gap-3 rounded-full bg-white px-7 py-4 text-sm font-bold text-rythme-black transition hover:bg-white/85">{{ $slide['cta'] }} <span aria-hidden="true">→</span></a>
                                    <a href="#categories" class="inline-flex items-center gap-3 text-sm font-semibold text-white transition hover:text-gold">
                                        Browse collections <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="hero-pagination swiper-pagination !bottom-10"></div>
            <button type="button" class="hero-prev absolute bottom-8 right-24 z-20 hidden h-12 w-12 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold hover:text-gold sm:flex" aria-label="Previous slide">←</button>
            <button type="button" class="hero-next absolute bottom-8 right-8 z-20 hidden h-12 w-12 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold hover:text-gold sm:flex" aria-label="Next slide">→</button>
        </div>
        <a href="#categories" class="absolute bottom-8 left-8 z-20 hidden items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-white/60 lg:flex">
            <span class="flex h-10 w-6 justify-center rounded-full border border-white/30 pt-2"><span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gold"></span></span> Scroll to discover
        </a>
    </section>
@endif
