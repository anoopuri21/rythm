# Phase 8: New Arrivals Section (Bento Grid)

Phase 1-7 are complete. Now build the New Arrivals section with bento grid layout.

## File to Edit: `resources/views/home/_new-arrivals.blade.php`

Replace the placeholder content with:

```html
{{-- New Arrivals Section --}}
<section id="new-arrivals" class="py-20 md:py-28 bg-rythme-cream overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="text-center mb-16 arrivals-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Just Arrived —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-rythme-black">
                Fresh Additions to Our <span class="text-gold-gradient">Collection</span>
            </h2>
        </div>

        {{-- Bento Grid --}}
        @php
            $newArrivals = [
                [
                    'name' => 'Martin D-28 Acoustic Guitar',
                    'brand' => 'Martin',
                    'price' => 189999,
                    'mrp' => 215000,
                    'image' => 'https://images.unsplash.com/photo-1550985616-10810253b84d?w=800&q=80',
                    'size' => 'large',
                ],
                [
                    'name' => 'Nord Stage 3 Keyboard',
                    'brand' => 'Nord',
                    'price' => 325000,
                    'mrp' => 350000,
                    'image' => 'https://images.unsplash.com/photo-1552422535-c45813c61732?w=600&q=80',
                    'size' => 'small',
                ],
                [
                    'name' => 'DW Design Series Drums',
                    'brand' => 'DW',
                    'price' => 145000,
                    'mrp' => 165000,
                    'image' => 'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?w=600&q=80',
                    'size' => 'small',
                ],
                [
                    'name' => 'Neumann U87 Studio Mic',
                    'brand' => 'Neumann',
                    'price' => 285000,
                    'mrp' => 320000,
                    'image' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=600&q=80',
                    'size' => 'small',
                ],
                [
                    'name' => 'Taylor 814ce Guitar',
                    'brand' => 'Taylor',
                    'price' => 275000,
                    'mrp' => 299000,
                    'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=600&q=80',
                    'size' => 'small',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 arrivals-grid">
            
            {{-- Large Featured Card (Left - spans 2 rows) --}}
            <div class="arrival-card md:row-span-2 relative rounded-2xl overflow-hidden group cursor-pointer min-h-[400px] md:min-h-0 img-zoom-hover">
                <img src="{{ $newArrivals[0]['image'] }}" alt="{{ $newArrivals[0]['name'] }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                
                {{-- NEW Badge with pulse --}}
                <div class="absolute top-4 left-4 z-10">
                    <span class="relative flex items-center">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold opacity-40"></span>
                        <span class="relative bg-gold text-rythme-black font-inter text-xs font-bold px-4 py-1.5 rounded-full">NEW</span>
                    </span>
                </div>
                
                {{-- Wishlist --}}
                <button class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white hover:text-rythme-red transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </button>
                
                {{-- Content --}}
                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 z-10">
                    <p class="font-inter text-gold text-xs uppercase tracking-wider mb-1">{{ $newArrivals[0]['brand'] }}</p>
                    <h3 class="font-playfair text-xl md:text-2xl font-bold text-white mb-3">{{ $newArrivals[0]['name'] }}</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="font-inter font-bold text-xl text-gold">₹{{ number_format($newArrivals[0]['price']) }}</span>
                        <span class="font-inter text-sm text-white/50 line-through">₹{{ number_format($newArrivals[0]['mrp']) }}</span>
                    </div>
                    <button class="bg-gold text-rythme-black font-inter font-semibold px-6 py-3 rounded-full text-sm hover:bg-gold-light transition-all duration-300 btn-gold-glow relative z-10">
                        Add to Cart
                    </button>
                </div>
            </div>

            {{-- Small Cards (Right - 2x2 grid) --}}
            @for($i = 1; $i <= 4; $i++)
                <div class="arrival-card relative rounded-2xl overflow-hidden group cursor-pointer min-h-[200px] md:min-h-[240px] img-zoom-hover">
                    <img src="{{ $newArrivals[$i]['image'] }}" alt="{{ $newArrivals[$i]['name'] }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    
                    {{-- NEW Badge --}}
                    <div class="absolute top-3 left-3 z-10">
                        <span class="bg-gold text-rythme-black font-inter text-xs font-bold px-3 py-1 rounded-full">NEW</span>
                    </div>
                    
                    {{-- Content --}}
                    <div class="absolute bottom-0 left-0 right-0 p-4 z-10">
                        <p class="font-inter text-gold text-xs uppercase tracking-wider mb-1">{{ $newArrivals[$i]['brand'] }}</p>
                        <h3 class="font-inter font-semibold text-sm text-white line-clamp-1 mb-2">{{ $newArrivals[$i]['name'] }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="font-inter font-bold text-base text-gold">₹{{ number_format($newArrivals[$i]['price']) }}</span>
                            <span class="font-inter text-xs text-white/50 line-through">₹{{ number_format($newArrivals[$i]['mrp']) }}</span>
                        </div>
                    </div>
                    
                    {{-- Hover Overlay --}}
                    <div class="absolute inset-0 bg-gold/0 group-hover:bg-gold/10 transition-all duration-500"></div>
                </div>
            @endfor

        </div>

        {{-- View All CTA --}}
        <div class="text-center mt-12">
            <a href="/shop?sort=newest" class="inline-flex items-center gap-2 font-inter font-semibold text-gold hover:text-gold-dark transition-colors duration-300 group">
                View All New Arrivals
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
// NEW ARRIVALS SECTION ANIMATIONS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.arrivals-header', {
            scrollTrigger: {
                trigger: '#new-arrivals',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        // Bento cards from different directions
        const arrivalCards = document.querySelectorAll('.arrival-card');
        arrivalCards.forEach((card, index) => {
            const directions = [
                { x: -60, y: 0 },   // left
                { x: 0, y: -60 },   // top
                { x: 60, y: 0 },    // right
                { x: 0, y: 60 },    // bottom
                { x: -60, y: 60 },  // bottom-left
            ];
            const dir = directions[index % directions.length];
            
            gsap.from(card, {
                scrollTrigger: {
                    trigger: '.arrivals-grid',
                    start: 'top 85%',
                    toggleActions: 'play none none none',
                },
                opacity: 0,
                x: dir.x,
                y: dir.y,
                duration: 0.8,
                delay: index * 0.15,
                ease: 'power2.out',
            });
        });
    }
});
Expected Result:
Cream background section
Bento grid: 1 large card (left, spans 2 rows) + 4 small cards (right, 2x2)
"NEW" badge with pulse animation on large card
All cards: image background, gradient overlay, product info at bottom
Hover: image zooms, slight gold tint overlay
Cards animate from different directions on scroll
Mobile: single column stack
