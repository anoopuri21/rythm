# Phase 4: Best Sellers / Trending Products Section

Phase 1-3 are complete. Now build the Best Sellers section with dark background and product cards.

## File to Edit: `resources/views/home/_bestsellers.blade.php`

Replace the placeholder content with:

```html
{{-- Best Sellers Section --}}
<section id="bestsellers" class="relative py-20 md:py-28 bg-rythme-black overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="text-center mb-12 bestseller-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Best Sellers —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Most Loved by <span class="text-gold-gradient">Musicians</span>
            </h2>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex flex-wrap justify-center gap-2 md:gap-4 mb-12 bestseller-tabs" x-data="{ activeTab: 'all' }">
            @php
                $tabs = [
                    ['id' => 'all', 'label' => 'All'],
                    ['id' => 'guitars', 'label' => 'Guitars'],
                    ['id' => 'keyboards', 'label' => 'Keyboards'],
                    ['id' => 'drums', 'label' => 'Drums'],
                    ['id' => 'pro-audio', 'label' => 'Pro Audio'],
                ];
            @endphp
            
            @foreach($tabs as $tab)
                <button 
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' ? 'bg-gold text-rythme-black' : 'bg-rythme-black-muted text-white/70 hover:text-white'"
                    class="font-inter text-sm font-medium px-6 py-2.5 rounded-full transition-all duration-300"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Products Grid / Swiper --}}
        @php
            $bestSellers = [
                [
                    'name' => 'Fender Player Stratocaster Electric Guitar',
                    'brand' => 'Fender',
                    'image' => 'https://images.unsplash.com/photo-1564186763535-ebb21ef5277f?w=400&q=80',
                    'mrp' => 72999,
                    'price' => 59999,
                    'rating' => 4.5,
                    'reviews' => 128,
                    'badge' => 'BEST SELLER',
                    'badge_color' => 'bg-gold text-rythme-black',
                ],
                [
                    'name' => 'Yamaha PSR-E473 Portable Keyboard',
                    'brand' => 'Yamaha',
                    'image' => 'https://images.unsplash.com/photo-1552422535-c45813c61732?w=400&q=80',
                    'mrp' => 24999,
                    'price' => 19999,
                    'rating' => 4.8,
                    'reviews' => 256,
                    'badge' => 'TOP RATED',
                    'badge_color' => 'bg-green-500 text-white',
                ],
                [
                    'name' => 'Roland TD-17KVX Electronic Drum Kit',
                    'brand' => 'Roland',
                    'image' => 'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?w=400&q=80',
                    'mrp' => 145000,
                    'price' => 124999,
                    'rating' => 4.7,
                    'reviews' => 89,
                    'badge' => 'SALE',
                    'badge_color' => 'bg-rythme-red text-white',
                ],
                [
                    'name' => 'Audio-Technica AT2020 Condenser Microphone',
                    'brand' => 'Audio-Technica',
                    'image' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=400&q=80',
                    'mrp' => 12999,
                    'price' => 9999,
                    'rating' => 4.6,
                    'reviews' => 342,
                    'badge' => 'BEST SELLER',
                    'badge_color' => 'bg-gold text-rythme-black',
                ],
                [
                    'name' => 'Gibson Les Paul Standard Electric Guitar',
                    'brand' => 'Gibson',
                    'image' => 'https://images.unsplash.com/photo-1550985616-10810253b84d?w=400&q=80',
                    'mrp' => 245000,
                    'price' => 219999,
                    'rating' => 4.9,
                    'reviews' => 67,
                    'badge' => 'PREMIUM',
                    'badge_color' => 'bg-gold-dark text-white',
                ],
                [
                    'name' => 'Casio CT-X5000 61-Key Portable Keyboard',
                    'brand' => 'Casio',
                    'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?w=400&q=80',
                    'mrp' => 34999,
                    'price' => 28999,
                    'rating' => 4.4,
                    'reviews' => 156,
                    'badge' => 'SALE',
                    'badge_color' => 'bg-rythme-red text-white',
                ],
                [
                    'name' => 'Shure SM58 Dynamic Vocal Microphone',
                    'brand' => 'Shure',
                    'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&q=80',
                    'mrp' => 11999,
                    'price' => 8999,
                    'rating' => 4.8,
                    'reviews' => 489,
                    'badge' => 'BEST SELLER',
                    'badge_color' => 'bg-gold text-rythme-black',
                ],
                [
                    'name' => 'Ibanez RG Series Electric Guitar',
                    'brand' => 'Ibanez',
                    'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=400&q=80',
                    'mrp' => 45999,
                    'price' => 38999,
                    'rating' => 4.5,
                    'reviews' => 203,
                    'badge' => 'NEW',
                    'badge_color' => 'bg-blue-500 text-white',
                ],
            ];
        @endphp

        {{-- Desktop Grid --}}
        <div class="hidden md:grid md:grid-cols-3 lg:grid-cols-4 gap-6 bestseller-grid">
            @foreach($bestSellers as $index => $product)
                <div class="bestseller-card bg-white rounded-2xl overflow-hidden card-hover-lift group" style="animation-delay: {{ $index * 0.1 }}s">
                    
                    {{-- Image Container --}}
                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                        <img 
                            src="{{ $product['image'] }}" 
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy"
                        >
                        
                        {{-- Badge --}}
                        <span class="absolute top-3 left-3 {{ $product['badge_color'] }} font-inter text-xs font-bold px-3 py-1 rounded-full">
                            {{ $product['badge'] }}
                        </span>
                        
                        {{-- Wishlist Heart --}}
                        <button class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-400 hover:text-rythme-red hover:bg-white transition-all duration-300 opacity-0 group-hover:opacity-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                        
                        {{-- Quick Add Button (appears on hover) --}}
                        <div class="absolute bottom-0 left-0 right-0 p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-500">
                            <button class="w-full bg-gold text-rythme-black font-inter font-semibold text-sm py-2.5 rounded-xl hover:bg-gold-light transition-colors duration-300">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    
                    {{-- Product Info --}}
                    <div class="p-4">
                        {{-- Brand --}}
                        <p class="font-inter text-xs text-rythme-warm-gray uppercase tracking-wider mb-1">{{ $product['brand'] }}</p>
                        
                        {{-- Product Name --}}
                        <h3 class="font-inter font-semibold text-sm text-rythme-black line-clamp-2 mb-2 group-hover:text-gold-dark transition-colors duration-300">
                            {{ $product['name'] }}
                        </h3>
                        
                        {{-- Rating --}}
                        <div class="flex items-center gap-1 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($product['rating']))
                                    <svg class="w-4 h-4 text-gold" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @elseif($i - $product['rating'] < 1)
                                    <svg class="w-4 h-4 text-gold" fill="currentColor" viewBox="0 0 20 20" style="clip-path: inset(0 50% 0 0);"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endif
                            @endfor
                            <span class="font-inter text-xs text-rythme-warm-gray ml-1">({{ $product['reviews'] }})</span>
                        </div>
                        
                        {{-- Price --}}
                        <div class="flex items-center gap-2">
                            <span class="font-inter font-bold text-lg text-rythme-red">₹{{ number_format($product['price']) }}</span>
                            <span class="font-inter text-sm text-rythme-warm-gray line-through">₹{{ number_format($product['mrp']) }}</span>
                            <span class="font-inter text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                {{ round((($product['mrp'] - $product['price']) / $product['mrp']) * 100) }}% OFF
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mobile Swiper --}}
        <div class="md:hidden">
            <div class="swiper bestseller-swiper">
                <div class="swiper-wrapper">
                    @foreach($bestSellers as $product)
                        <div class="swiper-slide">
                            <div class="bg-white rounded-2xl overflow-hidden card-hover-lift group">
                                
                                {{-- Image --}}
                                <div class="relative aspect-square overflow-hidden bg-gray-100">
                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover" loading="lazy">
                                    <span class="absolute top-3 left-3 {{ $product['badge_color'] }} font-inter text-xs font-bold px-3 py-1 rounded-full">{{ $product['badge'] }}</span>
                                    <button class="absolute top-3 right-3 w-9 h-9 bg-white/90 rounded-full flex items-center justify-center text-gray-400 hover:text-rythme-red">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    </button>
                                </div>
                                
                                {{-- Info --}}
                                <div class="p-4">
                                    <p class="font-inter text-xs text-rythme-warm-gray uppercase tracking-wider mb-1">{{ $product['brand'] }}</p>
                                    <h3 class="font-inter font-semibold text-sm text-rythme-black line-clamp-2 mb-2">{{ $product['name'] }}</h3>
                                    <div class="flex items-center gap-2">
                                        <span class="font-inter font-bold text-lg text-rythme-red">₹{{ number_format($product['price']) }}</span>
                                        <span class="font-inter text-xs text-rythme-warm-gray line-through">₹{{ number_format($product['mrp']) }}</span>
                                    </div>
                                    <button class="w-full mt-3 bg-gold text-rythme-black font-inter font-semibold text-sm py-2.5 rounded-xl hover:bg-gold-light transition-colors">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bestseller-pagination flex justify-center mt-6"></div>
            </div>
        </div>

        {{-- View All CTA --}}
        <div class="text-center mt-12">
            <a href="/shop?sort=bestsellers" class="inline-flex items-center gap-2 font-inter font-semibold text-gold hover:text-gold-light transition-colors duration-300 group">
                View All Best Sellers
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>

    </div>
</section>
Also append to resources/js/app.js:
JavaScript

// ================================================
// BEST SELLERS SECTION SCRIPTS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // Mobile Swiper for Best Sellers
    const bestsellerSwiperEl = document.querySelector('.bestseller-swiper');
    if (bestsellerSwiperEl) {
        new Swiper('.bestseller-swiper', {
            slidesPerView: 1.3,
            spaceBetween: 16,
            centeredSlides: false,
            pagination: {
                el: '.bestseller-pagination',
                clickable: true,
            },
            breakpoints: {
                480: { slidesPerView: 1.8, spaceBetween: 16 },
                640: { slidesPerView: 2.3, spaceBetween: 20 },
            },
        });
    }

    // GSAP Animations
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.bestseller-header', {
            scrollTrigger: {
                trigger: '#bestsellers',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        gsap.from('.bestseller-tabs', {
            scrollTrigger: {
                trigger: '#bestsellers',
                start: 'top 75%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 30,
            duration: 0.6,
            delay: 0.3,
            ease: 'power2.out',
        });

        gsap.from('.bestseller-card', {
            scrollTrigger: {
                trigger: '.bestseller-grid',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 60,
            scale: 0.95,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power2.out',
        });
    }
});
Expected Result:
Dark black background section with white product cards
Gold section header with gradient text
Filter tabs (visual only for now, no real filtering)
4-column product card grid on desktop, 3 on tablet
Product cards: image, badge, wishlist heart, brand, name, rating stars, price with discount
"Add to Cart" button slides up on hover
Card lifts on hover with shadow
Mobile: horizontal swiper carousel
Scroll animations for header, tabs, and cards
