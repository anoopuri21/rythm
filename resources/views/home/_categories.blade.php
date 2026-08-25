@php $sec = $homeSections['categories'] ?? null; @endphp
@php
    $categories = config('catalog.categories', []);
    // CAROUSEL — admin-driven: products marked Trending
    $trending = $homepage['trending'] ?? collect();
@endphp

{{-- ============================================================
     Explore by Category — PINNED horizontal scroll (P2 design)
     Section pin hoti hai (sticky), cards left→right move karte
     hain (scroll-driven translateX), end pe unpin. Mobile =
     horizontal touch scroll (pin off). Reduced-motion = grid.
     ============================================================ --}}
<section id="categories" class="pin relative overflow-hidden bg-rythme-cream">
    {{-- Instrument line-art shapes --}}
    <div class="pin__shapes" aria-hidden="true">
        <svg class="pinsh ps-1" viewBox="0 0 24 24"><circle cx="12" cy="17.5" r="5"/><line x1="12" y1="12.5" x2="12" y2="3.5"/><line x1="9.5" y1="5" x2="14.5" y2="5"/></svg>
        <svg class="pinsh ps-2" viewBox="0 0 24 24"><ellipse cx="12" cy="15.5" rx="8" ry="5.5"/><line x1="12" y1="10" x2="12" y2="4.5"/><circle cx="12" cy="3.6" r="1.3"/></svg>
        <svg class="pinsh ps-3" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><line x1="12" y1="12" x2="12" y2="18.5"/><line x1="8.5" y1="18.5" x2="15.5" y2="18.5"/></svg>
        <svg class="pinsh ps-4" viewBox="0 0 24 24"><path d="M9.5 17.5V6.5L18 4.5v11"/><ellipse cx="7.6" cy="17.5" rx="2" ry="1.6"/><ellipse cx="16" cy="15.5" rx="2" ry="1.6"/></svg>
    </div>

    <div class="pin__stage">
        {{-- Head --}}
        <div class="pin__head">
            <p class="section-kicker" data-reveal="up">{{ $sec->kicker ?? 'Explore by category' }}</p>
            <h2 class="section-title" data-reveal="up">
                @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Find your <em>instrument.</em>@endif
            </h2>
            <p class="section__lead" data-reveal="up">Keep scrolling — every category gets its moment on stage.</p>
        </div>

        {{-- Horizontal track (scroll-driven) --}}
        <div class="pin__viewport" id="cat-viewport">
            <div class="pin__track" id="cat-track" data-total="{{ count($categories) }}">
                @foreach($categories as $category)
                    <a href="/shop?category={{ $category['slug'] }}" class="gcard" data-reveal aria-label="{{ $category['name'] }} — {{ $category['count'] }}">
                        <div class="gcard__img">
                            {{-- Image: Bajaao product imagery (project rule) — object-contain, NO hover scale --}}
                            <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} — real product photo from Bajaao" width="800" height="800"
                                 loading="lazy" decoding="async">
                        </div>
                        <div class="gcard__body">
                            <p class="gcard__count">{{ $category['count'] }}</p>
                            <h3 class="gcard__name">{{ $category['name'] }}</h3>
                            <span class="gcard__cta">Explore <span aria-hidden="true">→</span></span>
                        </div>
                    </a>
                @endforeach

                {{-- End card --}}
                <a href="/shop" class="gcard gcard--end" data-reveal>
                    <div>
                        <span class="gcard__end-icon" aria-hidden="true">→</span>
                        <h3>See everything</h3>
                        <p>Browse the full collection of guitars, keys, drums and pro audio.</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- HUD: hint + progress + counter --}}
        <div class="pin__hud">
            <span class="pin__hint">SCROLL <b aria-hidden="true">→</b></span>
            <div class="pin__bar"><span id="pin-progress"></span></div>
            <span class="pin__count" id="pin-count">01 / {{ count($categories) }}</span>
        </div>
    </div>
</section>

{{-- ===== Products slider (below the pinned section) ===== --}}
<div class="relative bg-rythme-cream pb-24 pt-20 sm:pb-32">
    <div class="relative z-[1] mx-auto max-w-7xl px-5 sm:px-8" data-reveal="up">
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="section-kicker">Fresh picks from Bajaao's bestsellers</p>
                <h3 class="font-playfair text-2xl sm:text-3xl">Popular right <em>now.</em></h3>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" class="products-prev products-nav-btn" aria-label="Previous products">←</button>
                <button type="button" class="products-next products-nav-btn" aria-label="Next products">→</button>
            </div>
        </div>

        <div class="products-swiper swiper">
            <div class="swiper-wrapper">
                @forelse($trending as $product)
                    <div class="swiper-slide h-auto">
                        <article class="group flex h-full flex-col overflow-hidden rounded-xl border border-[#E8E8E8] bg-white transition hover:border-black">
                            {{-- Image: admin product image — object-fit: contain (kabhi cut nahi hota) --}}
                            <div class="relative m-3 aspect-square overflow-hidden rounded-lg bg-[#f7f7f7]">
                                <img src="{{ $product->heroImage() ?? 'https://placehold.co/800x800/f7f7f7/999?text='.rawurlencode($product->name) }}" alt="{{ $product->name }} — real product photo from Bajaao" width="800" height="800"
                                     class="h-full w-full object-contain p-5 transition-transform duration-700 group-hover:scale-105"
                                     loading="lazy" decoding="async">
                                @if($loop->first)
                                    <span class="absolute left-3 top-3 rounded-full bg-black px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Bestseller</span>
                                @endif
                            </div>
                            {{-- Body --}}
                            <div class="flex flex-1 flex-col p-5 pt-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-rythme-warm-gray">{{ $product->brand?->name }}</p>
                                <h3 class="mt-1.5 line-clamp-2 min-h-11 text-sm font-semibold leading-5 text-rythme-black">{{ $product->name }}</h3>
                                <div class="mt-2 flex items-center gap-1.5 text-xs">
                                    <span class="tracking-tight text-black">★★★★★</span>
                                    <span class="text-rythme-warm-gray">({{ rand(20, 200) }})</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                                    <span class="text-lg font-bold text-rythme-black">₹{{ number_format((float) $product->price) }}</span>
                                    <a href="/product/{{ $product->slug }}" class="inline-flex h-9 items-center gap-1.5 rounded-full bg-black px-4 text-xs font-bold text-white transition hover:bg-black">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                        Add
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="swiper-slide"><p class="py-10 text-center text-sm text-rythme-warm-gray">No trending products marked yet.</p></div>
                @endforelse
            </div>
            <div class="products-pagination swiper-pagination"></div>
        </div>
    </div>
</div>
