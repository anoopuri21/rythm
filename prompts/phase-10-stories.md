# Phase 10: Latest Stories / Blog Section

Phase 1-9 are complete. Now build the Latest Stories section.

## File to Edit: `resources/views/home/_stories.blade.php`

Replace the placeholder content with:

```html
{{-- Latest Stories Section --}}
<section id="stories" class="py-20 md:py-28 bg-rythme-cream overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="text-center mb-16 stories-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Latest Stories —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-rythme-black">
                Tips, Reviews & <span class="text-gold-gradient">Inspiration</span>
            </h2>
        </div>

        {{-- Blog Cards --}}
        @php
            $stories = [
                [
                    'category' => 'Buying Guide',
                    'category_color' => 'bg-gold/10 text-gold-dark',
                    'title' => 'How to Choose Your First Acoustic Guitar: A Complete Guide',
                    'excerpt' => 'Everything you need to know before buying your first guitar. From body shapes to tonewoods, we cover it all.',
                    'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=600&q=80',
                    'date' => 'Dec 15, 2024',
                    'read_time' => '8 min read',
                ],
                [
                    'category' => 'Product Review',
                    'category_color' => 'bg-rythme-red/10 text-rythme-red',
                    'title' => 'Yamaha PSR-E473 Review: Best Budget Keyboard of 2024?',
                    'excerpt' => 'We put the latest Yamaha portable keyboard through its paces. Here is our honest verdict.',
                    'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?w=600&q=80',
                    'date' => 'Dec 10, 2024',
                    'read_time' => '6 min read',
                ],
                [
                    'category' => 'Tips & Tricks',
                    'category_color' => 'bg-green-500/10 text-green-700',
                    'title' => '10 Essential Accessories Every Drummer Needs in Their Kit',
                    'excerpt' => 'From sticks to cymbal stands, here are the must-have accessories that will elevate your drumming.',
                    'image' => 'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?w=600&q=80',
                    'date' => 'Dec 5, 2024',
                    'read_time' => '5 min read',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            @foreach($stories as $index => $story)
                <article class="story-card bg-white rounded-2xl overflow-hidden card-hover-lift group cursor-pointer">
                    
                    {{-- Image --}}
                    <div class="relative h-56 overflow-hidden img-zoom-hover">
                        <img 
                            src="{{ $story['image'] }}" 
                            alt="{{ $story['title'] }}"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                        {{-- Category Badge --}}
                        <span class="absolute top-4 left-4 {{ $story['category_color'] }} font-inter text-xs font-semibold px-3 py-1.5 rounded-full">
                            {{ $story['category'] }}
                        </span>
                    </div>
                    
                    {{-- Content --}}
                    <div class="p-6">
                        {{-- Title --}}
                        <h3 class="font-playfair text-lg md:text-xl font-bold text-rythme-black mb-3 line-clamp-2 group-hover:text-gold-dark transition-colors duration-300">
                            {{ $story['title'] }}
                        </h3>
                        
                        {{-- Excerpt --}}
                        <p class="font-inter text-sm text-rythme-warm-gray leading-relaxed line-clamp-2 mb-4">
                            {{ $story['excerpt'] }}
                        </p>
                        
                        {{-- Meta + Read More --}}
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="font-inter text-xs text-rythme-warm-gray">{{ $story['date'] }}</span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span class="font-inter text-xs text-rythme-warm-gray">{{ $story['read_time'] }}</span>
                            </div>
                            <span class="font-inter text-sm font-semibold text-gold group-hover:text-gold-dark transition-colors flex items-center gap-1">
                                Read
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- View All CTA --}}
        <div class="text-center mt-12">
            <a href="/blog" class="inline-flex items-center gap-2 font-inter font-semibold text-gold hover:text-gold-dark transition-colors duration-300 group">
                Read More Stories
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
// STORIES SECTION ANIMATIONS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.stories-header', {
            scrollTrigger: {
                trigger: '#stories',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        gsap.from('.story-card', {
            scrollTrigger: {
                trigger: '.story-card',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 60,
            duration: 0.6,
            stagger: 0.2,
            ease: 'power2.out',
        });
    }
});
Expected Result:
Cream background with 3 blog cards in a row
Each card: image (with zoom on hover), category badge, title, excerpt, date, read time
Card lifts on hover with shadow
Title turns gold on hover
"Read" link with arrow animation
Scroll animations with stagger
Responsive: 1 col mobile, 2 col tablet, 3 col desktop
