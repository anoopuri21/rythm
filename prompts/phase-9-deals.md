# Phase 9: Deals / Offers Banner Section with Countdown

Phase 1-8 are complete. Now build the Deals section with countdown timer.

## File to Edit: `resources/views/home/_deals.blade.php`

Replace the placeholder content with:

```html
{{-- Deals Section --}}
<section id="deals" class="relative py-20 md:py-28 overflow-hidden">

    {{-- Background --}}
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=1920&q=70');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-rythme-black/95 via-rythme-black/80 to-rythme-black/60"></div>

    {{-- Animated Background Elements --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-10 right-10 w-72 h-72 bg-gold/5 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-rythme-red/5 rounded-full blur-3xl animate-float" style="animation-delay: 3s;"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        <div class="max-w-3xl mx-auto text-center deals-content">

            {{-- Fire Emoji --}}
            <div class="text-5xl mb-6">🔥</div>

            {{-- Small Label --}}
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Limited Time Offer —</p>

            {{-- Main Heading --}}
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-4">
                MEGA MUSIC <span class="text-gold-gradient">SALE</span>
            </h2>

            {{-- Sub Heading --}}
            <p class="font-inter text-xl md:text-2xl text-white/80 mb-3">
                UP TO <span class="font-bebas text-5xl md:text-6xl text-gold mx-2">40%</span> OFF
            </p>
            <p class="font-inter text-base text-white/60 mb-10">
                on Selected Premium Instruments
            </p>

            {{-- Countdown Timer --}}
            <div class="flex items-center justify-center gap-3 md:gap-6 mb-12" id="countdown-timer">
                
                {{-- Days --}}
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl w-16 h-16 md:w-24 md:h-24 flex items-center justify-center mb-2">
                        <span id="countdown-days" class="font-bebas text-3xl md:text-5xl text-white">00</span>
                    </div>
                    <span class="font-inter text-xs text-white/50 uppercase tracking-wider">Days</span>
                </div>

                <span class="font-bebas text-2xl md:text-4xl text-gold mt-[-20px]">:</span>

                {{-- Hours --}}
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl w-16 h-16 md:w-24 md:h-24 flex items-center justify-center mb-2">
                        <span id="countdown-hours" class="font-bebas text-3xl md:text-5xl text-white">00</span>
                    </div>
                    <span class="font-inter text-xs text-white/50 uppercase tracking-wider">Hours</span>
                </div>

                <span class="font-bebas text-2xl md:text-4xl text-gold mt-[-20px]">:</span>

                {{-- Minutes --}}
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl w-16 h-16 md:w-24 md:h-24 flex items-center justify-center mb-2">
                        <span id="countdown-mins" class="font-bebas text-3xl md:text-5xl text-white">00</span>
                    </div>
                    <span class="font-inter text-xs text-white/50 uppercase tracking-wider">Mins</span>
                </div>

                <span class="font-bebas text-2xl md:text-4xl text-gold mt-[-20px]">:</span>

                {{-- Seconds --}}
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl w-16 h-16 md:w-24 md:h-24 flex items-center justify-center mb-2">
                        <span id="countdown-secs" class="font-bebas text-3xl md:text-5xl text-gold">00</span>
                    </div>
                    <span class="font-inter text-xs text-white/50 uppercase tracking-wider">Secs</span>
                </div>

            </div>

            {{-- CTA Button --}}
            <a href="/deals" class="inline-flex items-center gap-3 bg-gold text-rythme-black font-inter font-bold px-10 py-4 rounded-full text-base md:text-lg tracking-wide hover:bg-gold-light transition-all duration-300 btn-gold-glow relative z-10">
                Grab The Deal
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>

        </div>

    </div>
</section>
Also append to resources/js/app.js:
JavaScript

// ================================================
// DEALS SECTION - COUNTDOWN TIMER
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // Set countdown target: 7 days from now (will be dynamic via admin later)
    const countdownTarget = new Date();
    countdownTarget.setDate(countdownTarget.getDate() + 7);

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = countdownTarget.getTime() - now;

        if (distance < 0) {
            // Sale ended
            document.getElementById('countdown-days').textContent = '00';
            document.getElementById('countdown-hours').textContent = '00';
            document.getElementById('countdown-mins').textContent = '00';
            document.getElementById('countdown-secs').textContent = '00';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((distance % (1000 * 60)) / 1000);

        const daysEl = document.getElementById('countdown-days');
        const hoursEl = document.getElementById('countdown-hours');
        const minsEl = document.getElementById('countdown-mins');
        const secsEl = document.getElementById('countdown-secs');

        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minsEl) minsEl.textContent = String(mins).padStart(2, '0');
        if (secsEl) secsEl.textContent = String(secs).padStart(2, '0');
    }

    // Update every second
    updateCountdown();
    setInterval(updateCountdown, 1000);

    // GSAP Animations
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.deals-content', {
            scrollTrigger: {
                trigger: '#deals',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 60,
            duration: 1,
            ease: 'power2.out',
        });

        gsap.from('#countdown-timer > div', {
            scrollTrigger: {
                trigger: '#countdown-timer',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            scale: 0.5,
            duration: 0.6,
            stagger: 0.15,
            ease: 'back.out(1.7)',
        });
    }
});
Expected Result:
Dark cinematic banner with concert background image
Gradient overlay from left (darker) to right
Floating blurred gold and red circles in background
"MEGA MUSIC SALE" heading with gold gradient
"40%" in large Bebas Neue font
Live countdown timer: Days:Hours:Mins:Secs
Glass-morphism countdown boxes (transparent bg + blur + border)
Seconds counter updates every second
Gold "Grab The Deal" CTA button with glow effect
Scroll animations
