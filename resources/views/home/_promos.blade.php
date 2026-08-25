@php
    // PROMO BANNERS — admin-driven (homepage_blocks section_key=promo)
    // Reference style: 3 big image blocks + heading + "Shop now".
    // Image: block media OR fallback (deals/why/brand assets).
    $promos = $homepage['promos'] ?? collect();
    $fallbackImages = ['images/deals-banner.jpg', 'images/why-rythme.jpg', 'images/brand-feature.jpg'];
@endphp

@if($promos->isNotEmpty())
    <section id="promos" class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                @foreach($promos as $i => $promo)
                    <a href="{{ $promo->content ?: '/shop' }}"
                       class="promo-card group relative block min-h-[320px] overflow-hidden rounded-2xl bg-ink md:min-h-[380px]"
                       aria-label="{{ $promo->title }}">
                        {{-- Image: admin block image, fallback to committed asset --}}
                        <img src="{{ $promo->getFirstMediaUrl('image') ?: asset($fallbackImages[$i % 3]) }}"
                             alt="{{ $promo->title }}" width="800" height="600"
                             class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                             loading="lazy" decoding="async">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>

                        {{-- Content --}}
                        <div class="relative z-10 flex h-full min-h-[320px] flex-col justify-end p-6 md:min-h-[380px] sm:p-7">
                            @if($promo->subtitle)
                                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-white/60">{{ $promo->subtitle }}</p>
                            @endif
                            <h3 class="mt-2 font-playfair text-2xl font-bold leading-tight text-white sm:text-3xl">{{ $promo->title }}</h3>
                            <span class="mt-4 inline-flex w-max items-center gap-2 rounded-full bg-white px-5 py-2.5 text-xs font-bold text-black transition group-hover:bg-black group-hover:text-white">
                                Shop now <span aria-hidden="true">→</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
