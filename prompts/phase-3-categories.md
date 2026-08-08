# Phase 3: Featured Categories Section

Phase 1 and 2 are complete. Now build the Featured Categories section.

## File to Edit: `resources/views/home/_categories.blade.php`

Replace the placeholder content with:

```html
{{-- Featured Categories Section --}}
<section id="categories" class="relative py-20 md:py-28 bg-rythme-cream overflow-hidden">

    {{-- Floating Musical Notes Background --}}
    <div class="absolute inset-0 pointer-events-none">
        <span class="music-note" style="top: 10%; left: 5%;">♪</span>
        <span class="music-note" style="top: 30%; right: 8%;">♫</span>
        <span class="music-note" style="top: 60%; left: 12%;">♩</span>
        <span class="music-note" style="top: 80%; right: 15%;">♬</span>
        <span class="music-note" style="top: 45%; left: 85%;">♪</span>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Section Header --}}
        <div class="text-center mb-16 category-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Explore By Category —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-rythme-black">
                Find Your Perfect <span class="text-gold-gradient">Instrument</span>
            </h2>
        </div>

        {{-- Categories Grid --}}
        @php
            $categories = [
                [
                    'name' => 'Guitars',
                    'count' => 342,
                    'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=600&q=80',
                    'slug' => 'guitars',
                ],
                [
                    'name' => 'Keyboards & Pianos',
                    'count' => 186,
                    'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?w=600&q=80',
                    'slug' => 'keyboards-pianos',
                ],
                [
                    'name' => 'Drums & Percussion',
                    'count' => 154,
                    'image' => 'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?w=600&q=80',
                    'slug' => 'drums-percussion',
                ],
                [
                    'name' => 'Pro Audio',
                    'count' => 267,
                    'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=600&q=80',
                    'slug' => 'pro-audio',
                ],
                [
                    'name' => 'Live Sound',
                    'count' => 98,
                    'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&q=80',
                    'slug' => 'live-sound',
                ],
                [
                    'name' => 'Wind Instruments',
                    'count' => 76,
                    'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=600&q=80',
                    'slug' => 'wind-instruments',
                ],
                [
                    'name' => 'Indian Instruments',
                    'count' => 123,
                    'image' => 'https://images.unsplash.com/photo-1621982205012-7b516e477eff?w=600&q=80',
                    'slug' => 'indian-instruments',
                ],
                [
                    'name' => 'Accessories',
                    'count' => 534,
                    'image' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=600&q=80',
                    'slug' => 'accessories',
                ],
                [
                    'name' => 'Recording',
                    'count' => 189,
                    'image' => 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?w=600&q=80',
                    'slug' => 'recording',
                ],
                [
                    'name' => 'All Brands',
                    'count' => 50,
                    'image' => 'https://images.unsplash.com/photo-1507838153414-b4b713384a76?w=600&q=80',
                    'slug' => 'brands',
                ],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6 lg:gap-8">
            @foreach($categories as $index => $category)
                <a href="/category/{{ $category['slug'] }}" 
                   class="category-card group relative aspect-square rounded-2xl overflow-hidden cursor-pointer img-zoom-hover"
                   style="animation-delay: {{ $index * 0.1 }}s">
                    
                    {{-- Background Image --}}
                    <img 
                        src="{{ $category['image'] }}" 
                        alt="{{ $category['name'] }}"
                        class="absolute inset-0 w-full h-full object-cover"
                        loading="lazy"
                    >
                    
                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent transition-all duration-500 group-hover:from-black/60"></div>
                    
                    {{-- Gold Border on Hover --}}
                    <div class="absolute inset-0 rounded-2xl ring-0 ring-gold transition-all duration-500 group-hover:ring-2"></div>
                    
                    {{-- Content --}}
                    <div class="absolute bottom-0 left-0 right-0 p-4 md:p-5 z-10">
                        <h3 class="font-playfair text-base md:text-lg lg:text-xl font-bold text-white mb-1 group-hover:text-gold-light transition-colors duration-300">
                            {{ $category['name'] }}
                        </h3>
                        <p class="font-inter text-xs md:text-sm text-white/60">
                            {{ $category['count'] }} Products
                        </p>
                        
                        {{-- Hover Underline --}}
                        <div class="w-0 h-0.5 bg-gold mt-2 transition-all duration-500 group-hover:w-full"></div>
                    </div>
                    
                </a>
            @endforeach
        </div>

        {{-- View All CTA --}}
        <div class="text-center mt-12">
            <a href="/categories" class="inline-flex items-center gap-2 font-inter font-semibold text-gold hover:text-gold-dark transition-colors duration-300 group">
                View All Categories
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
// CATEGORIES SECTION ANIMATIONS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        // Category header animation
        gsap.from('.category-header', {
            scrollTrigger: {
                trigger: '#categories',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        // Category cards stagger animation
        gsap.from('.category-card', {
            scrollTrigger: {
                trigger: '.category-card',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 60,
            scale: 0.9,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power2.out',
        });
    }
});
Expected Result:
Clean cream background with subtle floating musical notes
Section header with gold gradient text on "Instrument"
10 category cards in 5-column grid (desktop)
Each card: full image background, gradient overlay, text at bottom
Hover: image zooms, gold border appears, text turns gold-light, underline animates
Cards animate in with stagger effect when scrolled into view
"View All Categories" link with arrow animation on hover
Responsive: 2 cols mobile, 3 cols tablet, 5 cols desktop
