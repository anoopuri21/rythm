@php
    $categories = config('catalog.categories', []);
@endphp

<section id="categories" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    @include('components.instrument-decor')
    <span class="music-note left-[5%] top-20">♪</span><span class="music-note right-[8%] top-40">♫</span>
    <div class="relative z-[1] relative z-[1] mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-14 flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal="up">
            <div>
                <p class="section-kicker">Explore by category</p>
                <h2 class="section-title">Find your <em>instrument.</em></h2>
                <p class="mt-5 max-w-xl text-base leading-7 text-rythme-warm-gray">From your first chord to your hundredth gig — shop real gear from the brands musicians trust.</p>
            </div>
            <a href="/shop" class="text-link shrink-0">View all products <span>↗</span></a>
        </div>

        {{-- Custom CSS grid: flex-wrap + centered rows, 1:1 square cards, images object-contain (kabhi cut nahi) --}}
        <div class="cat-grid">
            @foreach($categories as $index => $category)
                <a href="/category/{{ $category['slug'] }}"
                   class="cat-card group"
                   data-reveal="up" aria-label="{{ $category['name'] }} — {{ $category['count'] }}">
                    {{-- Image: Bajaao product imagery (project rule: product images from Bajaao) --}}
                    <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} — real product photo from Bajaao" width="800" height="800"
                         class="cat-card-img"
                         loading="lazy" decoding="async">
                    <div class="cat-card-overlay" aria-hidden="true"></div>
                    <div class="cat-card-body">
                        <p class="cat-card-count">{{ $category['count'] }}</p>
                        <h3 class="cat-card-title">{{ $category['name'] }}</h3>
                        <p class="cat-card-tag">{{ $category['tagline'] }}</p>
                        <span class="cat-card-arrow" aria-hidden="true">→</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- ===== Products slider — real Bajaao products, smooth transition ===== --}}
        <div class="relative mt-20" data-reveal="up">
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
                    @foreach(config('catalog.carousel') as $product)
                        <div class="swiper-slide h-auto">
                            <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-black/5 bg-white shadow-[0_4px_16px_rgba(0,0,0,0.05)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_24px_48px_rgba(0,0,0,0.14)]">
                                {{-- Image: Bajaao real product — object-fit: contain (kabhi cut nahi hota) --}}
                                <div class="relative m-3 aspect-square overflow-hidden rounded-2xl bg-[#f7f7f7]">
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }} — real product photo from Bajaao" width="800" height="800"
                                         class="h-full w-full object-contain p-5 transition-transform duration-700 group-hover:scale-105"
                                         loading="lazy" decoding="async">
                                    @if($loop->first)
                                        <span class="absolute left-3 top-3 rounded-full bg-rythme-red px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Bestseller</span>
                                    @endif
                                    <button type="button" class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white text-rythme-black shadow-sm transition hover:bg-rythme-red hover:text-white" aria-label="Add {{ $product['name'] }} to wishlist">♡</button>
                                </div>
                                {{-- Body --}}
                                <div class="flex flex-1 flex-col p-5 pt-3">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-rythme-red">{{ $product['brand'] }}</p>
                                    <h3 class="mt-1.5 line-clamp-2 min-h-11 text-sm font-semibold leading-5 text-rythme-black">{{ $product['name'] }}</h3>
                                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                                        <span class="tracking-tight text-rythme-red">★★★★★</span>
                                        <span class="text-rythme-warm-gray">({{ $product['reviews'] ?? 0 }})</span>
                                    </div>
                                    <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                                        <span class="text-lg font-bold text-rythme-black">₹{{ number_format($product['price']) }}</span>
                                        <a href="/product/{{ Str::slug($product['name']) }}" class="inline-flex h-9 items-center gap-1.5 rounded-full bg-rythme-red px-4 text-xs font-bold text-white transition hover:bg-rythme-red-dark">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                            Add
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                <div class="products-pagination swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
