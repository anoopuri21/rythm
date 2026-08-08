# Phase 7: Numbers / Stats Counter Section

Phase 1-6 are complete. Now build the Numbers section with animated counters and parallax background.

## File to Edit: `resources/views/home/_numbers.blade.php`

Replace the placeholder content with:

```html
{{-- Numbers Section --}}
<section id="numbers" class="relative py-20 md:py-28 overflow-hidden">

    {{-- Parallax Background --}}
    <div class="absolute inset-0 parallax-section" style="background-image: url('https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1920&q=60');"></div>
    <div class="absolute inset-0 bg-rythme-black/85"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Section Header --}}
        <div class="text-center mb-16 numbers-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Rythme in Numbers —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Trusted by <span class="text-gold-gradient">Musicians</span> Nationwide
            </h2>
        </div>

        {{-- Stats Grid --}}
        @php
            $stats = [
                ['number' => 10000, 'suffix' => '+', 'label' => 'Happy Customers', 'icon' => '😊'],
                ['number' => 500, 'suffix' => '+', 'label' => 'Brands Listed', 'icon' => '⭐'],
                ['number' => 50, 'suffix' => '+', 'label' => 'Cities Served', 'icon' => '📍'],
                ['number' => 100, 'suffix' => '%', 'label' => 'Genuine Products', 'icon' => '✅'],
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">
            @foreach($stats as $index => $stat)
                <div class="text-center number-item group" data-count="{{ $stat['number'] }}" data-suffix="{{ $stat['suffix'] }}">
                    
                    {{-- Icon --}}
                    <div class="text-4xl mb-4">{{ $stat['icon'] }}</div>
                    
                    {{-- Number --}}
                    <div class="flex items-center justify-center gap-1 mb-3">
                        <span class="counter-number font-bebas text-5xl md:text-6xl lg:text-7xl text-gold font-bold" id="counter-{{ $index }}">0</span>
                        <span class="font-bebas text-4xl md:text-5xl lg:text-6xl text-gold">{{ $stat['suffix'] }}</span>
                    </div>
                    
                    {{-- Label --}}
                    <p class="font-inter text-sm md:text-base text-white/70 tracking-wide">{{ $stat['label'] }}</p>
                    
                    {{-- Underline --}}
                    <div class="w-12 h-0.5 bg-gold/30 mx-auto mt-4 group-hover:w-20 group-hover:bg-gold transition-all duration-500"></div>
                </div>
            @endforeach
        </div>

    </div>
</section>
Also append to resources/js/app.js:
JavaScript

// ================================================
// NUMBERS SECTION - COUNTUP ANIMATION
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // CountUp Animation
    const numberItems = document.querySelectorAll('.number-item');
    let hasCounted = false;

    if (numberItems.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !hasCounted) {
                    hasCounted = true;
                    
                    numberItems.forEach((item, index) => {
                        const target = parseInt(item.dataset.count);
                        const counterEl = document.getElementById(`counter-${index}`);
                        
                        if (counterEl) {
                            let current = 0;
                            const increment = target / 60;
                            const duration = 2000;
                            const stepTime = duration / 60;
                            
                            const timer = setInterval(() => {
                                current += increment;
                                if (current >= target) {
                                    current = target;
                                    clearInterval(timer);
                                }
                                counterEl.textContent = Math.floor(current).toLocaleString();
                            }, stepTime);
                        }
                    });
                }
            });
        }, { threshold: 0.3 });

        const numbersSection = document.getElementById('numbers');
        if (numbersSection) {
            observer.observe(numbersSection);
        }
    }

    // GSAP Animations
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.numbers-header', {
            scrollTrigger: {
                trigger: '#numbers',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        gsap.from('.number-item', {
            scrollTrigger: {
                trigger: '.number-item',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 40,
            scale: 0.9,
            duration: 0.6,
            stagger: 0.15,
            ease: 'power2.out',
        });
    }
});
Expected Result:
Dark section with parallax concert/stage background image
Semi-transparent black overlay for readability
4 stat counters in a row (2x2 on mobile)
Numbers animate from 0 to target when section enters viewport
Bebas Neue font for numbers (big, bold, gold)
Animation triggers only once
Hover: underline expands and turns gold
Scroll animations for header and items
