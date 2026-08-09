@php
    $categories = config('catalog.categories', []);
@endphp

<section id="categories" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    <span class="music-note left-[5%] top-20">♪</span><span class="music-note right-[8%] top-40">♫</span>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-14 flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal="up">
            <div>
                <p class="section-kicker">Explore by category</p>
                <h2 class="section-title">Find your <em>instrument.</em></h2>
                <p class="mt-5 max-w-xl text-base leading-7 text-rythme-warm-gray">From your first chord to your hundredth gig — shop real gear from the brands musicians trust.</p>
            </div>
            <a href="/shop" class="text-link shrink-0">View all products <span>↗</span></a>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4 lg:grid-rows-2">
            @foreach($categories as $index => $category)
                <a href="/category/{{ $category['slug'] }}"
                   class="cat-card group relative overflow-hidden rounded-3xl {{ $index === 0 ? 'col-span-2 row-span-2' : 'col-span-1' }} {{ in_array($index, [5, 7]) ? 'lg:col-span-2' : '' }}"
                   data-reveal="up" aria-label="{{ $category['name'] }} — {{ $category['count'] }}">
                    {{-- Image: Bajaao product imagery (project rule: product images from Bajaao) --}}
                    <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} — real product photo from Bajaao" width="800" height="800"
                         class="cat-card-img absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                         loading="lazy" decoding="async">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-black/10 transition-opacity duration-500 group-hover:from-black/90"></div>
                    <div class="cat-card-content absolute inset-x-0 bottom-0 p-5 sm:p-6">
                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-gold-light opacity-0 transition-all duration-500 group-hover:opacity-100">{{ $category['count'] }}</p>
                        <h3 class="font-playfair text-xl font-bold text-white sm:text-2xl {{ $index === 0 ? 'lg:text-4xl' : '' }}">{{ $category['name'] }}</h3>
                        <p class="mt-1 max-w-[26ch] text-xs leading-5 text-white/70 opacity-0 transition-all duration-500 group-hover:opacity-100 sm:text-sm">{{ $category['tagline'] }}</p>
                        <span class="mt-3 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/25 text-white transition-all duration-500 group-hover:translate-x-2 group-hover:border-gold group-hover:bg-gold group-hover:text-rythme-black" aria-hidden="true">→</span>
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
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
                <div class="products-pagination swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
