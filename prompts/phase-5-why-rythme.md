# Phase 5: Why Rythme Section (USP + Parallax)

Phase 1-4 are complete. Now build the "Why Choose Rythme" section with parallax background.

## File to Edit: `resources/views/home/_why-rythme.blade.php`

Replace the placeholder content with:

```html
{{-- Why Rythme Section --}}
<section id="why-rythme" class="relative py-20 md:py-28 overflow-hidden">
    
    {{-- Parallax Background --}}
    <div class="absolute inset-0 parallax-section" style="background-image: url('https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=1920&q=60');">
    </div>
    <div class="absolute inset-0 bg-rythme-cream/90"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Section Header --}}
        <div class="text-center mb-16 why-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Why Choose Rythme? —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-rythme-black">
                Your Music Journey <span class="text-gold-gradient">Starts Here</span>
            </h2>
        </div>

        {{-- USP Cards Grid --}}
        @php
            $features = [
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
                    'title' => '100% Authentic Products',
                    'desc' => 'Every instrument comes with brand warranty and authenticity guarantee. No fakes, no compromises.',
                ],
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>',
                    'title' => 'Free Shipping All India',
                    'desc' => 'Enjoy free delivery on all orders across India. Fast, safe, and insured shipping on every purchase.',
                ],
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    'title' => 'Best Price Guarantee',
                    'desc' => 'Found it cheaper elsewhere? We will match it. Get the best deals on premium musical instruments.',
                ],
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
                    'title' => 'Expert Guidance',
                    'desc' => 'Our team of musicians and experts will help you choose the perfect instrument for your needs.',
                ],
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>',
                    'title' => 'Easy Returns & Refunds',
                    'desc' => 'Not satisfied? Return within 7 days for a full refund. No questions asked, hassle-free process.',
                ],
                [
                    'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
                    'title' => '24/7 Support',
                    'desc' => 'Round the clock customer support via chat, email, and phone. We are always here to help you.',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            @foreach($features as $index => $feature)
                <div class="why-card group bg-white/80 backdrop-blur-sm rounded-2xl p-6 md:p-8 border border-gray-100 hover:border-gold/30 transition-all duration-500 relative overflow-hidden" style="animation-delay: {{ $index * 0.1 }}s">
                    
                    {{-- Gold left border on hover --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gold scale-y-0 group-hover:scale-y-100 transition-transform duration-500 origin-top"></div>
                    
                    {{-- Icon --}}
                    <div class="w-14 h-14 bg-gold/10 rounded-xl flex items-center justify-center text-gold mb-5 group-hover:bg-gold group-hover:text-white transition-all duration-500 why-icon">
                        {!! $feature['icon'] !!}
                    </div>
                    
                    {{-- Title --}}
                    <h3 class="font-playfair text-xl font-bold text-rythme-black mb-3 group-hover:text-gold-dark transition-colors duration-300">
                        {{ $feature['title'] }}
                    </h3>
                    
                    {{-- Description --}}
                    <p class="font-inter text-sm text-rythme-warm-gray leading-relaxed">
                        {{ $feature['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>

    </div>
</section>
Also append to resources/js/app.js:
JavaScript

// ================================================
// WHY RYTHME SECTION ANIMATIONS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.why-header', {
            scrollTrigger: {
                trigger: '#why-rythme',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        gsap.from('.why-card', {
            scrollTrigger: {
                trigger: '.why-card',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 60,
            duration: 0.6,
            stagger: 0.15,
            ease: 'power2.out',
        });

        // Icon spin animation on scroll
        gsap.from('.why-icon', {
            scrollTrigger: {
                trigger: '.why-card',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            rotation: -180,
            scale: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: 'back.out(1.7)',
        });
    }
});
Expected Result:
Light cream section with parallax background image (faded guitar)
6 USP cards in 3x2 grid
Each card: icon in gold circle, title, description
Hover: gold left border appears, icon bg turns gold, title changes color
Icons spin in on scroll
Cards stagger fade up on scroll
Responsive: 1 col mobile, 2 col tablet, 3 col desktop
