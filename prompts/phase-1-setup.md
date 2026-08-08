# Phase 1: Project Setup + Master Layout + Navbar

You are a senior Laravel developer building a cinematic e-commerce website for "Rythme Music Store" - a single-seller musical instrument store. The design should feel premium, elegant, and cinematic with a light cream/white base theme and gold+black+red accent colors.

## STEP 1: Create Laravel Project

Create a new Laravel project. Make sure you are using the latest stable Laravel version (12+). Set up the project with the following:

### Terminal Commands (run in order):
```bash
composer create-project laravel/laravel rythme-music-store
cd rythme-music-store

Configure .env file:
APP_NAME="Rythme Music Store"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rythme_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database

CLOUDINARY_URL=cloudinary://placeholder:placeholder@placeholder
CLOUDINARY_UPLOAD_PRESET=rythme_unsigned

STEP 2: Install All Dependencies
Composer Packages:
composer require livewire/livewire
composer require robsontenorio/mary
composer require filament/filament:"^3.2"
composer require cloudinary-labs/cloudinary-laravel
composer require razorpay/razorpay

Run Filament Install:
php artisan filament:install --panels

Run Mary UI Install
php artisan mary:install

NPM Packages:
npm install -D tailwindcss @tailwindcss/forms
npm install gsap
npm install lenis
npm install swiper
npm install countup.js

Run Migrations:
php artisan migrate

STEP 3: Configure Tailwind CSS
File: tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                gold: {
                    light: '#F5D061',
                    DEFAULT: '#D4A843',
                    dark: '#B8860B',
                },
                rythme: {
                    black: '#0A0A0A',
                    'black-soft': '#1A1A1A',
                    'black-muted': '#2D2D2D',
                    red: '#C41E3A',
                    'red-dark': '#8B0000',
                    'red-light': '#DC3545',
                    cream: '#FFFDF7',
                    'cream-dark': '#F5F0E8',
                    'warm-white': '#FAFAF5',
                    'warm-gray': '#6B6B6B',
                },
            },
            fontFamily: {
                playfair: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
                inter: ['"Inter"', ...defaultTheme.fontFamily.sans],
                bebas: ['"Bebas Neue"', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'bounce-slow': 'bounce 2s infinite',
                'marquee': 'marquee 30s linear infinite',
                'fade-in': 'fadeIn 0.6s ease-out forwards',
                'slide-up': 'slideUp 0.6s ease-out forwards',
                'float': 'float 6s ease-in-out infinite',
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0%)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};

STEP 4: Configure CSS
File: resources/css/app.css
@import 'tailwindcss';

/* Google Fonts Import */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700&family=Bebas+Neue&display=swap');

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #FFFDF7;
}

::-webkit-scrollbar-thumb {
    background: #D4A843;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #B8860B;
}

/* Smooth scroll base */
html {
    scroll-behavior: auto; /* Lenis handles smooth scroll */
}

/* Swiper custom styles */
.swiper-pagination-bullet {
    width: 12px !important;
    height: 12px !important;
    background: rgba(255, 255, 255, 0.5) !important;
    opacity: 1 !important;
    transition: all 0.3s ease !important;
    margin: 0 4px !important;
}

.swiper-pagination-bullet-active {
    background: #D4A843 !important;
    width: 32px !important;
    border-radius: 6px !important;
}

/* Hero section video */
.hero-video {
    object-fit: cover;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
}

/* Gold gradient text */
.text-gold-gradient {
    background: linear-gradient(135deg, #F5D061, #D4A843, #B8860B);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Parallax section base */
.parallax-section {
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

/* Section reveal animation base */
.reveal-section {
    opacity: 0;
    transform: translateY(60px);
    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.reveal-section.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Card hover lift effect */
.card-hover-lift {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
}

.card-hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Gold glow button */
.btn-gold-glow {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-gold-glow::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(245, 208, 97, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.btn-gold-glow:hover::before {
    width: 300px;
    height: 300px;
}

.btn-gold-glow:hover {
    box-shadow: 0 0 30px rgba(212, 168, 67, 0.4);
}

/* Image zoom on hover */
.img-zoom-hover {
    overflow: hidden;
}

.img-zoom-hover img {
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.img-zoom-hover:hover img {
    transform: scale(1.1);
}

/* Navbar transparent to solid */
.navbar-transparent {
    background-color: transparent;
    backdrop-filter: none;
}

.navbar-solid {
    background-color: rgba(255, 253, 247, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
}

/* Musical note floating animation */
.music-note {
    position: absolute;
    opacity: 0.05;
    animation: float 6s ease-in-out infinite;
    font-size: 2rem;
    color: #D4A843;
    pointer-events: none;
}

.music-note:nth-child(2) { animation-delay: 1s; }
.music-note:nth-child(3) { animation-delay: 2s; }
.music-note:nth-child(4) { animation-delay: 3s; }
.music-note:nth-child(5) { animation-delay: 4s; }

STEP 5: Configure JavaScript
File: resources/js/app.js
import './bootstrap';

// Lenis Smooth Scroll
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// Initialize Lenis
const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    direction: 'vertical',
    gestureDirection: 'vertical',
    smooth: true,
    mouseMultiplier: 1,
    smoothTouch: false,
    touchMultiplier: 2,
    infinite: false,
});

// Connect Lenis to GSAP ScrollTrigger
lenis.on('scroll', ScrollTrigger.update);

gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
});

gsap.ticker.lagSmoothing(0);

// Make gsap and ScrollTrigger available globally for section scripts
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

// Navbar scroll behavior
document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('navbar');
    
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.remove('navbar-transparent');
                navbar.classList.add('navbar-solid');
                navbar.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('text-white');
                    link.classList.add('text-rythme-black');
                });
                const logo = navbar.querySelector('.nav-logo');
                if (logo) {
                    logo.classList.remove('text-white');
                    logo.classList.add('text-gold');
                }
            } else {
                navbar.classList.add('navbar-transparent');
                navbar.classList.remove('navbar-solid');
                navbar.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.add('text-white');
                    link.classList.remove('text-rythme-black');
                });
                const logo = navbar.querySelector('.nav-logo');
                if (logo) {
                    logo.classList.add('text-white');
                    logo.classList.remove('text-gold');
                }
            }
        });
    }
});

STEP 6: Create Master Layout
File: resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Rythme Music Store - Feel The Music, Own The Sound')</title>
    <meta name="description" content="@yield('meta_description', 'Rythme Music Store - Premium musical instruments, guitars, keyboards, drums, pro audio and more. Shop authentic instruments from top brands.')">
    
    <!-- Open Graph -->
    <meta property="og:title" content="@yield('title', 'Rythme Music Store')">
    <meta property="og:description" content="@yield('meta_description', 'Premium musical instruments store')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    
    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('styles')
</head>
<body class="font-inter bg-rythme-cream text-rythme-black antialiased overflow-x-hidden">
    
    <!-- Navbar -->
    @include('components.navbar')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Swiper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- CountUp JS CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.8.0/countUp.umd.min.js"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    @stack('scripts')
</body>
</html>

STEP 7: Create Navbar Component
File: resources/views/components/navbar.blade.php
HTML

<!-- Navbar -->
<nav id="navbar" class="fixed top-0 left-0 w-full z-50 navbar-transparent transition-all duration-500" x-data="{ mobileMenu: false, searchOpen: false, categoryDropdown: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- Logo -->
            <a href="/" class="nav-logo text-white transition-colors duration-300">
                <span class="font-playfair text-2xl font-bold tracking-wider">RYTHME</span>
                <span class="hidden sm:inline-block text-xs font-inter tracking-widest ml-2 opacity-70">MUSIC STORE</span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center space-x-8">
                <a href="/" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                    Home
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/shop" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                    Shop
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                
                <!-- Categories Dropdown -->
                <div class="relative" @mouseenter="categoryDropdown = true" @mouseleave="categoryDropdown = false">
                    <button class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold flex items-center gap-1">
                        Categories
                        <svg class="w-4 h-4 transition-transform duration-300" :class="categoryDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Mega Menu -->
                    <div x-show="categoryDropdown" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-[600px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 grid grid-cols-2 gap-4"
                         style="display: none;">
                        
                        @php
                            $navCategories = [
                                ['name' => 'Guitars', 'icon' => '🎸', 'desc' => 'Acoustic, Electric, Bass'],
                                ['name' => 'Keyboards & Pianos', 'icon' => '🎹', 'desc' => 'Digital, Synthesizer, MIDI'],
                                ['name' => 'Drums & Percussion', 'icon' => '🥁', 'desc' => 'Acoustic, Electronic, Cajons'],
                                ['name' => 'Pro Audio', 'icon' => '🎤', 'desc' => 'Mics, Interface, Monitors'],
                                ['name' => 'Live Sound', 'icon' => '🔊', 'desc' => 'Speakers, Amps, DJ Gear'],
                                ['name' => 'Wind Instruments', 'icon' => '🎺', 'desc' => 'Flute, Saxophone, Trumpet'],
                                ['name' => 'Indian Instruments', 'icon' => '🪕', 'desc' => 'Tabla, Sitar, Harmonium'],
                                ['name' => 'Accessories', 'icon' => '🎵', 'desc' => 'Strings, Picks, Cases'],
                                ['name' => 'Recording', 'icon' => '🎧', 'desc' => 'Headphones, Studio Gear'],
                                ['name' => 'Brands', 'icon' => '⭐', 'desc' => 'Fender, Yamaha, Gibson'],
                            ];
                        @endphp
                        
                        @foreach($navCategories as $cat)
                            <a href="/category/{{ Str::slug($cat['name']) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-rythme-cream transition-colors duration-200 group">
                                <span class="text-2xl">{{ $cat['icon'] }}</span>
                                <div>
                                    <p class="font-inter font-semibold text-sm text-rythme-black group-hover:text-gold transition-colors">{{ $cat['name'] }}</p>
                                    <p class="font-inter text-xs text-rythme-warm-gray">{{ $cat['desc'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <a href="/brands" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                    Brands
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/deals" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                    Deals
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/contact" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                    Contact
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
            </div>
            
            <!-- Right Side Icons -->
            <div class="flex items-center space-x-4">
                
                <!-- Search -->
                <div class="relative">
                    <button @click="searchOpen = !searchOpen" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- Search Expanded -->
                    <div x-show="searchOpen" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 w-0"
                         x-transition:enter-end="opacity-100 w-72"
                         x-transition:leave="transition ease-in duration-200"
                         @click.away="searchOpen = false"
                         class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-lg p-3"
                         style="display: none;">
                        <input type="text" placeholder="Search instruments..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm font-inter focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold">
                    </div>
                </div>
                
                <!-- Wishlist -->
                <a href="/wishlist" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 relative hidden sm:block">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-rythme-red text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-inter font-bold">0</span>
                </a>
                
                <!-- Cart -->
                <a href="/cart" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-rythme-red text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-inter font-bold">0</span>
                </a>
                
                <!-- Account -->
                <a href="/account" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 hidden sm:block">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </a>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden nav-link text-white transition-colors duration-300 hover:text-gold p-2">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Drawer -->
    <div x-show="mobileMenu"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="lg:hidden bg-white shadow-2xl rounded-b-2xl"
         style="display: none;">
        <div class="px-6 py-8 space-y-4">
            <a href="/" class="block font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">Home</a>
            <a href="/shop" class="block font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">Shop</a>
            
            <!-- Mobile Categories -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">
                    Categories
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="pl-4 space-y-2 mt-2">
                    <a href="/category/guitars" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎸 Guitars</a>
                    <a href="/category/keyboards" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎹 Keyboards & Pianos</a>
                    <a href="/category/drums" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🥁 Drums & Percussion</a>
                    <a href="/category/pro-audio" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎤 Pro Audio</a>
                    <a href="/category/live-sound" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🔊 Live Sound</a>
                    <a href="/category/wind-instruments" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎺 Wind Instruments</a>
                    <a href="/category/indian-instruments" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🪕 Indian Instruments</a>
                    <a href="/category/accessories" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎵 Accessories</a>
                    <a href="/category/recording" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">🎧 Recording</a>
                    <a href="/category/brands" class="block text-sm text-rythme-warm-gray hover:text-gold transition-colors py-1">⭐ Brands</a>
                </div>
            </div>
            
            <a href="/brands" class="block font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">Brands</a>
            <a href="/deals" class="block font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">Deals</a>
            <a href="/contact" class="block font-inter font-medium text-rythme-black hover:text-gold transition-colors py-2">Contact</a>
            
            <hr class="border-gray-200">
            
            <div class="flex items-center space-x-6 pt-2">
                <a href="/wishlist" class="text-rythme-black hover:text-gold transition-colors flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    Wishlist
                </a>
                <a href="/account" class="text-rythme-black hover:text-gold transition-colors flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Account
                </a>
            </div>
        </div>
    </div>
</nav>
STEP 8: Create Home Controller
File: app/Http/Controllers/HomeController.php
PHP

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Hero mode: 'video' or 'slider' (will come from admin settings later)
        $heroMode = 'slider';
        
        return view('home.index', compact('heroMode'));
    }
}
STEP 9: Set Up Routes
File: routes/web.php
PHP

<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
STEP 10: Create Home Page Index View
File: resources/views/home/index.blade.php
HTML

@extends('layouts.app')

@section('title', 'Rythme Music Store - Feel The Music, Own The Sound')
@section('meta_description', 'Shop premium musical instruments at Rythme Music Store. Guitars, Keyboards, Drums, Pro Audio and more from top brands like Fender, Yamaha, Gibson. Free shipping all over India.')

@section('content')

    {{-- Section 1: Hero --}}
    @include('home._hero', ['heroMode' => $heroMode])

    {{-- Section 2: Featured Categories --}}
    @include('home._categories')

    {{-- Section 3: Best Sellers --}}
    @include('home._bestsellers')

    {{-- Section 4: Why Rythme --}}
    @include('home._why-rythme')

    {{-- Section 5: Brand Showcase --}}
    @include('home._brands')

    {{-- Section 6: Numbers --}}
    @include('home._numbers')

    {{-- Section 7: New Arrivals --}}
    @include('home._new-arrivals')

    {{-- Section 8: Deals/Offers --}}
    @include('home._deals')

    {{-- Section 9: Latest Stories --}}
    @include('home._stories')

    {{-- Section 10: Testimonials --}}
    @include('home._testimonials')

    {{-- Section 11: Footer --}}
    @include('home._footer')

@endsection
STEP 11: Create All Empty Partial Files
Create these files with placeholder content:

File: resources/views/home/_hero.blade.php
HTML

{{-- Hero Section - Phase 2 --}}
<section id="hero" class="relative h-screen w-full overflow-hidden bg-rythme-black">
    <div class="flex items-center justify-center h-full">
        <p class="text-white text-2xl font-playfair">Hero Section - Coming in Phase 2</p>
    </div>
</section>
File: resources/views/home/_categories.blade.php
HTML

{{-- Featured Categories Section - Phase 3 --}}
<section id="categories" class="py-20 bg-rythme-cream">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-rythme-black">Categories Section - Coming in Phase 3</p>
    </div>
</section>
File: resources/views/home/_bestsellers.blade.php
HTML

{{-- Best Sellers Section - Phase 4 --}}
<section id="bestsellers" class="py-20 bg-rythme-black">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-white">Best Sellers Section - Coming in Phase 4</p>
    </div>
</section>
File: resources/views/home/_why-rythme.blade.php
HTML

{{-- Why Rythme Section - Phase 5 --}}
<section id="why-rythme" class="py-20 bg-rythme-cream">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-rythme-black">Why Rythme Section - Coming in Phase 5</p>
    </div>
</section>
File: resources/views/home/_brands.blade.php
HTML

{{-- Brand Showcase Section - Phase 6 --}}
<section id="brands" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-rythme-black">Brand Showcase - Coming in Phase 6</p>
    </div>
</section>
File: resources/views/home/_numbers.blade.php
HTML

{{-- Numbers Section - Phase 7 --}}
<section id="numbers" class="py-20 bg-rythme-black">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-white">Numbers Section - Coming in Phase 7</p>
    </div>
</section>
File: resources/views/home/_new-arrivals.blade.php
HTML

{{-- New Arrivals Section - Phase 8 --}}
<section id="new-arrivals" class="py-20 bg-rythme-cream">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-rythme-black">New Arrivals - Coming in Phase 8</p>
    </div>
</section>
File: resources/views/home/_deals.blade.php
HTML

{{-- Deals Section - Phase 9 --}}
<section id="deals" class="py-20 bg-rythme-black">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-white">Deals Section - Coming in Phase 9</p>
    </div>
</section>
File: resources/views/home/_stories.blade.php
HTML

{{-- Latest Stories Section - Phase 10 --}}
<section id="stories" class="py-20 bg-rythme-cream">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-rythme-black">Latest Stories - Coming in Phase 10</p>
    </div>
</section>
File: resources/views/home/_testimonials.blade.php
HTML

{{-- Testimonials Section - Phase 11 --}}
<section id="testimonials" class="py-20 bg-rythme-black-soft">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair text-white">Testimonials - Coming in Phase 11</p>
    </div>
</section>
File: resources/views/home/_footer.blade.php
HTML

{{-- Footer Section - Phase 12 --}}
<footer id="footer" class="bg-rythme-black text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <p class="text-center text-xl font-playfair">Footer - Coming in Phase 12</p>
    </div>
</footer>
STEP 12: Verify Setup
Run these commands and make sure everything works:

Bash

npm run build
php artisan serve
Open http://localhost:8000 and verify:

Page loads with cream background
Navbar is visible at top (transparent)
All 11 section placeholders are visible when scrolling
Smooth scrolling works (Lenis)
Navbar changes from transparent to solid white on scroll
Mobile menu works
No console errors
Google fonts are loaded (Playfair Display visible in section texts)
Categories mega menu dropdown works on desktop
IMPORTANT NOTES:
Do NOT skip any step
Do NOT modify file paths
Do NOT add extra packages
Follow the exact color codes and class names
Make sure Alpine.js works (it comes with Livewire)
