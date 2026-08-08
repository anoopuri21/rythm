@php
    $slides = [
        ['image' => 'images/hero-guitar.jpg', 'eyebrow' => 'Craft your signature sound', 'title' => 'Feel the music.', 'accent' => 'Own the sound.', 'copy' => 'Handpicked instruments, expertly set up and delivered with care anywhere in India.', 'cta' => 'Explore instruments'],
        ['image' => 'images/hero-piano.jpg', 'eyebrow' => 'The keys to expression', 'title' => 'Every note.', 'accent' => 'Entirely yours.', 'copy' => 'From first melodies to concert stages, discover keys that move with your ambition.', 'cta' => 'Shop keyboards'],
        ['image' => 'images/hero-studio.jpg', 'eyebrow' => 'Build your perfect studio', 'title' => 'Capture the moment.', 'accent' => 'Keep it forever.', 'copy' => 'Professional recording essentials selected for clarity, character and lasting performance.', 'cta' => 'Explore pro audio'],
    ];
@endphp

<section id="hero" class="relative min-h-[760px] h-screen w-full overflow-hidden bg-rythme-black" aria-label="Featured collections">
    <div class="hero-swiper swiper h-full">
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
                <article class="swiper-slide relative overflow-hidden">
                    <img src="{{ asset($slide['image']) }}" alt="" class="hero-slide-image absolute inset-0 h-full w-full object-cover" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/65 to-black/10"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/30"></div>
                    <div class="relative z-10 mx-auto flex h-full max-w-7xl items-center px-5 sm:px-8 lg:px-12 pt-20">
                        <div class="hero-copy max-w-3xl text-white">
                            <p class="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.32em] text-gold-light">
                                <span class="h-px w-10 bg-gold"></span>{{ $slide['eyebrow'] }}
                            </p>
                            <h1 class="font-playfair text-5xl leading-[0.98] sm:text-7xl lg:text-[6.4rem]">
                                {{ $slide['title'] }}<br><em class="font-normal text-gold-gradient">{{ $slide['accent'] }}</em>
                            </h1>
                            <p class="mt-7 max-w-xl text-base leading-7 text-white/70 sm:text-lg">{{ $slide['copy'] }}</p>
                            <div class="mt-9 flex flex-wrap items-center gap-5">
                                <a href="/shop" class="btn-gold-glow inline-flex items-center gap-3 rounded-full bg-gold px-7 py-4 text-sm font-bold text-rythme-black transition hover:bg-gold-light">
                                    <span class="relative z-10">{{ $slide['cta'] }}</span><span aria-hidden="true" class="relative z-10">↗</span>
                                </a>
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
