Phase 1 is complete. The Laravel project is set up with Tailwind, Livewire, GSAP, Lenis, and Swiper. Now build the Hero Section.

## File to Edit: `resources/views/home/_hero.blade.php`

Replace the placeholder content with the following complete hero section:

```html
{{-- Hero Section --}}
<section id="hero" class="relative h-screen w-full overflow-hidden bg-rythme-black">

    {{-- ============================================ --}}
    {{-- MODE 1: VIDEO BACKGROUND --}}
    {{-- ============================================ --}}
    @if($heroMode === 'video')
    <div class="absolute inset-0 w-full h-full">
        <video 
            class="hero-video"
            autoplay 
            muted 
            loop 
            playsinline
            poster="https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=1920&q=80"
        >
            <source src="https://cdn.coverr.co/videos/coverr-a-]musician-playing-guitar-on-stage-1584/1080p.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- MODE 2: IMAGE CAROUSEL (SWIPER) --}}
    {{-- ============================================ --}}
    @if($heroMode === 'slider')
    <div class="swiper hero-swiper absolute inset-0 w-full h-full">
        <div class="swiper-wrapper">
            
            {{-- Slide 1 --}}
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=1920&q=80')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                    <p class="font-inter text-gold tracking-[0.3em] uppercase text-sm md:text-base mb-4 hero-subtitle opacity-0">♪ Welcome to</p>
                    <h1 class="font-playfair text-5xl sm:text-6xl md:text-7xl lg:text-8xl xl:text-9xl font-bold text-white mb-6 hero-title opacity-0">RYTHME</h1>
                    <p class="font-inter text-lg md:text-xl lg:text-2xl text-white/80 mb-10 max-w-2xl hero-desc opacity-0">Feel The Music, Own The Sound</p>
                    <div class="flex flex-col sm:flex-row gap-4 hero-buttons opacity-0">
                        <a href="/shop" class="btn-gold-glow bg-gold text-rythme-black font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:bg-gold-light transition-all duration-300 relative z-10">
                            Shop Now
                        </a>
                        <a href="#categories" class="border-2 border-white text-white font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:border-gold hover:text-gold transition-all duration-300">
                            Explore Categories
                        </a>
                    </div>
                </div>
            </div>

            {{-- Slide 2 --}}
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?w=1920&q=80')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                    <p class="font-inter text-gold tracking-[0.3em] uppercase text-sm md:text-base mb-4">♪ Discover</p>
                    <h1 class="font-playfair text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-white mb-6">Premium Keyboards</h1>
                    <p class="font-inter text-lg md:text-xl lg:text-2xl text-white/80 mb-10 max-w-2xl">From Beginners to Maestros</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/category/keyboards" class="btn-gold-glow bg-gold text-rythme-black font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:bg-gold-light transition-all duration-300 relative z-10">
                            Shop Keyboards
                        </a>
                        <a href="#categories" class="border-2 border-white text-white font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:border-gold hover:text-gold transition-all duration-300">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            {{-- Slide 3 --}}
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?w=1920&q=80')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                    <p class="font-inter text-gold tracking-[0.3em] uppercase text-sm md:text-base mb-4">♪ Experience</p>
                    <h1 class="font-playfair text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-white mb-6">Percussion Paradise</h1>
                    <p class="font-inter text-lg md:text-xl lg:text-2xl text-white/80 mb-10 max-w-2xl">Beat That Moves The Soul</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/category/drums" class="btn-gold-glow bg-gold text-rythme-black font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:bg-gold-light transition-all duration-300 relative z-10">
                            Shop Drums
                        </a>
                        <a href="#categories" class="border-2 border-white text-white font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:border-gold hover:text-gold transition-all duration-300">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            {{-- Slide 4 --}}
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=1920&q=80')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                    <p class="font-inter text-gold tracking-[0.3em] uppercase text-sm md:text-base mb-4">♪ Professional</p>
                    <h1 class="font-playfair text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-white mb-6">Pro Audio Gear</h1>
                    <p class="font-inter text-lg md:text-xl lg:text-2xl text-white/80 mb-10 max-w-2xl">Studio Quality, Delivered Home</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/category/pro-audio" class="btn-gold-glow bg-gold text-rythme-black font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:bg-gold-light transition-all duration-300 relative z-10">
                            Shop Pro Audio
                        </a>
                        <a href="#categories" class="border-2 border-white text-white font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:border-gold hover:text-gold transition-all duration-300">
                            View All
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Pagination --}}
        <div class="hero-pagination absolute bottom-24 left-1/2 -translate-x-1/2 z-20 flex items-center"></div>

        {{-- Navigation Arrows --}}
        <button class="hero-prev absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-gold hover:border-gold hover:text-rythme-black transition-all duration-300 hidden md:flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button class="hero-next absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white hover:bg-gold hover:border-gold hover:text-rythme-black transition-all duration-300 hidden md:flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- DARK OVERLAY (for video mode) --}}
    {{-- ============================================ --}}
    @if($heroMode === 'video')
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20 z-[1]"></div>
    
    {{-- Video Mode Content --}}
    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
        <p class="font-inter text-gold tracking-[0.3em] uppercase text-sm md:text-base mb-4 hero-subtitle opacity-0">♪ Welcome to</p>
        <h1 class="font-playfair text-5xl sm:text-6xl md:text-7xl lg:text-8xl xl:text-9xl font-bold text-white mb-6 hero-title opacity-0">RYTHME</h1>
        <p class="font-inter text-lg md:text-xl lg:text-2xl text-white/80 mb-10 max-w-2xl hero-desc opacity-0">Feel The Music, Own The Sound</p>
        <div class="flex flex-col sm:flex-row gap-4 hero-buttons opacity-0">
            <a href="/shop" class="btn-gold-glow bg-gold text-rythme-black font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:bg-gold-light transition-all duration-300 relative z-10">
                Shop Now
            </a>
            <a href="#categories" class="border-2 border-white text-white font-inter font-semibold px-8 py-4 rounded-full text-sm md:text-base tracking-wide hover:border-gold hover:text-gold transition-all duration-300">
                Explore Categories
            </a>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- SCROLL DOWN INDICATOR --}}
    {{-- ============================================ --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center gap-2 hero-scroll-indicator opacity-0">
        <span class="font-inter text-white/60 text-xs tracking-[0.2em] uppercase">Scroll</span>
        <div class="w-6 h-10 border-2 border-white/40 rounded-full flex justify-center">
            <div class="w-1.5 h-3 bg-gold rounded-full mt-2 animate-bounce-slow"></div>
        </div>
    </div>

</section>
Also add to: resources/js/app.js (append at the bottom)
Add this code at the end of the existing app.js file. Do NOT replace existing code, just append:

JavaScript

// ================================================
// HERO SECTION SCRIPTS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // Initialize Hero Swiper
    const heroSwiperEl = document.querySelector('.hero-swiper');
    if (heroSwiperEl) {
        const heroSwiper = new Swiper('.hero-swiper', {
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.hero-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.hero-next',
                prevEl: '.hero-prev',
            },
            loop: true,
            speed: 1000,
        });
    }

    // GSAP Hero Animations
    if (typeof gsap !== 'undefined') {
        const heroTl = gsap.timeline({ delay: 0.5 });

        // First slide animations (or video mode content)
        heroTl
            .to('.hero-subtitle', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power2.out',
            })
            .to('.hero-title', {
                opacity: 1,
                y: 0,
                duration: 1.2,
                ease: 'power3.out',
            }, '-=0.4')
            .to('.hero-desc', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power2.out',
            }, '-=0.6')
            .to('.hero-buttons', {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: 'power2.out',
            }, '-=0.4')
            .to('.hero-scroll-indicator', {
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out',
            }, '-=0.2');

        // Set initial states
        gsap.set(['.hero-subtitle', '.hero-title', '.hero-desc', '.hero-buttons'], {
            y: 40,
        });
    }
});
Expected Result:
Full-screen hero section covering entire viewport
Image carousel with 4 slides, fade effect, auto-play
Gold pagination dots at bottom
Navigation arrows on sides (desktop only)
"RYTHME" heading with elegant Playfair Display font
Two CTA buttons: gold solid + white outline
Scroll indicator at bottom with bouncing animation
GSAP animations: text reveals on page load
Responsive: works on mobile, tablet, desktop
If heroMode is changed to 'video', video background plays instead
