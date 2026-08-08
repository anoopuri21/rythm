# Phase 6: Brand Showcase Section

Phase 1-5 are complete. Now build the Brand Showcase section with logo marquee and featured brand banner.

## File to Edit: `resources/views/home/_brands.blade.php`

Replace the placeholder content with:

```html
{{-- Brand Showcase Section --}}
<section id="brands" class="py-20 md:py-28 bg-white overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="text-center mb-16 brand-header">
            <p class="font-inter text-gold text-sm tracking-[0.3em] uppercase mb-4">— Our Trusted Brands —</p>
            <h2 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-rythme-black">
                Partnered with the <span class="text-gold-gradient">World's Best</span>
            </h2>
        </div>

    </div>

    {{-- Brand Logo Marquee --}}
    @php
        $brands = [
            ['name' => 'Fender', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Fender_guitars_logo.svg/200px-Fender_guitars_logo.svg.png'],
            ['name' => 'Yamaha', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Yamaha_logo.svg/200px-Yamaha_logo.svg.png'],
            ['name' => 'Gibson', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/47/Gibson_Guitar_logo.svg/200px-Gibson_Guitar_logo.svg.png'],
            ['name' => 'Roland', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Roland_logo.svg/200px-Roland_logo.svg.png'],
            ['name' => 'Casio', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/CASIO_logo.svg/200px-CASIO_logo.svg.png'],
            ['name' => 'Ibanez', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/Ibanez_logo.svg/200px-Ibanez_logo.svg.png'],
            ['name' => 'Marshall', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Marshall_Amplification_logo.svg/200px-Marshall_Amplification_logo.svg.png'],
            ['name' => 'Shure', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e5/Shure_logo.svg/200px-Shure_logo.svg.png'],
            ['name' => 'JBL', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/JBL_logo.svg/200px-JBL_logo.svg.png'],
            ['name' => 'Audio-Technica', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Audio-Technica_Logo.svg/200px-Audio-Technica_Logo.svg.png'],
            ['name' => 'Zildjian', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Zildjian_Logo.svg/200px-Zildjian_Logo.svg.png'],
            ['name' => 'Sennheiser', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Sennheiser_logo.svg/200px-Sennheiser_logo.svg.png'],
        ];
    @endphp

    <div class="relative mb-16 brand-marquee">
        {{-- Gradient fade left --}}
        <div class="absolute left-0 top-0 bottom-0 w-20 md:w-40 bg-gradient-to-r from-white to-transparent z-10"></div>
        {{-- Gradient fade right --}}
        <div class="absolute right-0 top-0 bottom-0 w-20 md:w-40 bg-gradient-to-l from-white to-transparent z-10"></div>
        
        {{-- Scrolling Container --}}
        <div class="flex animate-marquee whitespace-nowrap">
            {{-- First set --}}
            @foreach($brands as $brand)
                <div class="flex-shrink-0 mx-8 md:mx-12 flex items-center justify-center h-20">
                    <img 
                        src="{{ $brand['logo'] }}" 
                        alt="{{ $brand['name'] }}"
                        class="h-8 md:h-10 w-auto object-contain filter grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-500 cursor-pointer"
                        title="{{ $brand['name'] }}"
                    >
                </div>
            @endforeach
            {{-- Duplicate set for seamless loop --}}
            @foreach($brands as $brand)
                <div class="flex-shrink-0 mx-8 md:mx-12 flex items-center justify-center h-20">
                    <img 
                        src="{{ $brand['logo'] }}" 
                        alt="{{ $brand['name'] }}"
                        class="h-8 md:h-10 w-auto object-contain filter grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-500 cursor-pointer"
                        title="{{ $brand['name'] }}"
                    >
                </div>
            @endforeach
        </div>
    </div>

    {{-- Featured Brand Spotlight Banner --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="brand-spotlight relative rounded-3xl overflow-hidden h-64 md:h-80 group cursor-pointer">
            {{-- Background --}}
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=1200&q=80')"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-rythme-black/90 via-rythme-black/60 to-transparent"></div>
            
            {{-- Content --}}
            <div class="relative z-10 h-full flex flex-col justify-center px-8 md:px-16 max-w-xl">
                <p class="font-inter text-gold text-xs tracking-[0.3em] uppercase mb-3">Featured Brand</p>
                <h3 class="font-playfair text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">Yamaha Collection</h3>
                <p class="font-inter text-white/70 text-sm md:text-base mb-6 hidden sm:block">Explore the complete range of Yamaha instruments. From keyboards to guitars, find your perfect match.</p>
                <a href="/brands/yamaha" class="inline-flex items-center gap-2 bg-gold text-rythme-black font-inter font-semibold px-6 py-3 rounded-full text-sm hover:bg-gold-light transition-all duration-300 w-fit btn-gold-glow relative z-10">
                    Shop Yamaha
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
        </div>
    </div>

</section>
Also append to resources/js/app.js:
JavaScript

// ================================================
// BRAND SHOWCASE SECTION ANIMATIONS
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        
        gsap.from('.brand-header', {
            scrollTrigger: {
                trigger: '#brands',
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            ease: 'power2.out',
        });

        gsap.from('.brand-marquee', {
            scrollTrigger: {
                trigger: '.brand-marquee',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            duration: 1,
            ease: 'power2.out',
        });

        gsap.from('.brand-spotlight', {
            scrollTrigger: {
                trigger: '.brand-spotlight',
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            opacity: 0,
            y: 40,
            scale: 0.98,
            duration: 0.8,
            ease: 'power2.out',
        });
    }
});
Expected Result:
White section with gold accented header
Auto-scrolling infinite logo marquee (CSS animation, no JS needed)
Logos: grayscale by default, full color on hover
Fade gradients on left and right edges of marquee
Featured brand banner: full width, dark gradient, Yamaha spotlight
Banner hover: background scales slightly
Scroll animations for all elements
