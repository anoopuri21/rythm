@php
    $categories = config('catalog.categories', []);
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
@endphp

<nav id="navbar" class="fixed top-0 left-0 z-50 w-full navbar-transparent transition-all duration-500"
     x-data="{ mobileMenu: false, searchOpen: false, catOpen: false, activeCat: 0 }"
     @keydown.escape.window="mobileMenu = false; searchOpen = false; catOpen = false">
    <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <div class="flex h-20 items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="nav-logo flex shrink-0 items-center gap-2.5 transition-colors duration-300" aria-label="{{ $brand }} home">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434"
                     class="h-9 w-auto drop-shadow-sm sm:h-10"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <span class="hidden font-playfair text-xl font-bold tracking-wide text-white xl:inline-flex" aria-hidden="true">RHYTHM<span class="text-gold"> EXPORTS</span></span>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden items-center gap-7 lg:flex xl:gap-9">
                <a href="{{ route('home') }}" class="nav-link text-white transition-colors duration-300 text-sm font-medium hover:text-gold relative group">
                    Home
                    <span class="absolute -bottom-1.5 left-0 h-0.5 w-0 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/shop" class="nav-link text-white transition-colors duration-300 text-sm font-medium hover:text-gold relative group">
                    Shop
                    <span class="absolute -bottom-1.5 left-0 h-0.5 w-0 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>

                {{-- Mega menu: Categories (Bajaao-style) --}}
                <div class="relative" @mouseenter="catOpen = true" @mouseleave="catOpen = false">
                    <button type="button" class="nav-link flex items-center gap-1.5 text-white transition-colors duration-300 text-sm font-medium hover:text-gold"
                            @click="catOpen = !catOpen" :aria-expanded="catOpen ? 'true' : 'false'" aria-haspopup="true">
                        Categories
                        <svg class="h-4 w-4 transition-transform duration-300" :class="catOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="catOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute left-1/2 top-full z-50 mt-4 w-[860px] -translate-x-1/2 rounded-3xl border border-black/5 bg-white p-7 shadow-[0_40px_90px_rgba(0,0,0,0.22)]"
                         @click.outside="catOpen = false">
                        <div class="grid grid-cols-[1.05fr_1.55fr] gap-8">
                            {{-- Category rail --}}
                            <div>
                                <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.28em] text-rythme-warm-gray">Shop by category</p>
                                <ul class="space-y-0.5">
                                    @foreach($categories as $index => $category)
                                        <li>
                                            <button type="button" @mouseenter="activeCat = {{ $index }}"
                                                    :class="activeCat === {{ $index }} ? 'bg-gold/10 text-rythme-black' : 'text-rythme-warm-gray hover:text-rythme-black'"
                                                    class="flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left text-sm font-semibold transition-colors duration-200">
                                                {{ $category['name'] }}
                                                <svg class="h-3.5 w-3.5 opacity-50" :class="activeCat === {{ $index }} ? 'opacity-100 text-gold-dark' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" /></svg>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="/shop" class="mt-5 inline-flex items-center gap-2 rounded-full bg-rythme-black px-5 py-2.5 text-xs font-bold text-white transition hover:bg-gold hover:text-rythme-black">
                                    View all products <span aria-hidden="true">→</span>
                                </a>
                            </div>

                            {{-- Subcategory panel --}}
                            <div class="relative overflow-hidden rounded-2xl bg-rythme-cream p-6">
                                @foreach($categories as $index => $category)
                                    <div x-show="activeCat === {{ $index }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0">
                                        <p class="font-playfair text-lg font-bold text-rythme-black">{{ $category['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-rythme-warm-gray">{{ $category['tagline'] }} · {{ $category['count'] }}</p>
                                        <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5">
                                            @foreach($category['children'] as $child)
                                                <li>
                                                    <a href="/category/{{ $child['slug'] }}" class="group flex items-center gap-2 text-sm text-rythme-warm-gray transition hover:text-gold-dark">
                                                        <span class="h-1 w-1 rounded-full bg-gold/60 transition group-hover:w-3"></span>{{ $child['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <a href="/category/{{ $category['slug'] }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-gold-dark transition hover:gap-3">
                                            Explore {{ $category['name'] }} <span aria-hidden="true">→</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <a href="/deals" class="nav-link text-white transition-colors duration-300 text-sm font-medium hover:text-gold relative group">
                    Deals
                    <span class="absolute -bottom-1.5 left-0 h-0.5 w-0 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/brands" class="nav-link text-white transition-colors duration-300 text-sm font-medium hover:text-gold relative group">
                    Brands
                    <span class="absolute -bottom-1.5 left-0 h-0.5 w-0 bg-gold transition-all duration-300 group-hover:w-full"></span>
                </a>
            </div>

            {{-- Icons --}}
            <div class="flex items-center gap-1 sm:gap-2">
                <div class="relative">
                    <button type="button" @click="searchOpen = !searchOpen" class="nav-link rounded-full p-2.5 text-white transition-colors duration-300 hover:bg-white/10" aria-label="Search" :aria-expanded="searchOpen ? 'true' : 'false'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                    <div x-cloak x-show="searchOpen" @click.outside="searchOpen = false"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-3 w-80 rounded-2xl bg-white p-3 shadow-2xl">
                        <label for="nav-search" class="sr-only">Search instruments</label>
                        <input id="nav-search" type="search" placeholder="Search guitars, keyboards, mics…" class="w-full rounded-xl border border-black/10 px-4 py-3 text-sm text-rythme-black outline-none transition focus:border-gold focus:ring-2 focus:ring-gold/30">
                    </div>
                </div>

                <a href="/wishlist" class="nav-link relative hidden rounded-full p-2.5 text-white transition-colors duration-300 hover:bg-white/10 sm:block" aria-label="Wishlist, 0 items">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-rythme-red text-[10px] font-bold text-white">0</span>
                </a>
                <a href="/cart" class="nav-link relative rounded-full p-2.5 text-white transition-colors duration-300 hover:bg-white/10" aria-label="Cart, 0 items">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-rythme-red text-[10px] font-bold text-white">0</span>
                </a>
                <a href="/account" class="nav-link hidden rounded-full p-2.5 text-white transition-colors duration-300 hover:bg-white/10 sm:block" aria-label="Account">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </a>

                <button type="button" @click="mobileMenu = true" class="lg:hidden rounded-full p-2.5 text-white transition-colors duration-300 hover:bg-white/10" aria-label="Open navigation menu" aria-controls="mobile-menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile drawer (left off-canvas) --}}
    <div x-cloak x-show="mobileMenu" x-transition.opacity.duration.250ms class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm lg:hidden" @click="mobileMenu = false" aria-hidden="true"></div>
    <div id="mobile-menu" x-cloak x-show="mobileMenu" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
         role="dialog" aria-modal="true" aria-label="Mobile navigation"
         class="fixed inset-y-0 left-0 z-[70] flex w-[88%] max-w-sm flex-col bg-white shadow-2xl lg:hidden">
        <div class="flex items-center justify-between border-b border-black/5 px-6 py-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" @click="mobileMenu = false">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-8 w-auto" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <span class="hidden font-playfair text-lg font-bold text-rythme-black" aria-hidden="true">RHYTHM <span class="text-gold-dark">EXPORTS</span></span>
            </a>
            <button type="button" @click="mobileMenu = false" class="rounded-full p-2 text-rythme-black transition hover:bg-black/5" aria-label="Close menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-6">
            <a href="{{ route('home') }}" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Home</a>
            <a href="/shop" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Shop All</a>

            {{-- Mobile categories accordion --}}
            <div class="mt-2" x-data="{ openCat: null }">
                <p class="px-4 pb-2 pt-4 text-[10px] font-bold uppercase tracking-[0.25em] text-rythme-warm-gray">Categories</p>
                @foreach($categories as $index => $category)
                    <div class="mb-1">
                        <button type="button" @click="openCat = openCat === {{ $index }} ? null : {{ $index }}"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10"
                                :aria-expanded="openCat === {{ $index }} ? 'true' : 'false'">
                            {{ $category['name'] }}
                            <svg class="h-4 w-4 text-gold-dark transition-transform duration-300" :class="openCat === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-cloak x-show="openCat === {{ $index }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-4 border-l border-gold/30 pl-4">
                            @foreach($category['children'] as $child)
                                <a href="/category/{{ $child['slug'] }}" @click="mobileMenu = false" class="block rounded-lg px-3 py-2 text-sm text-rythme-warm-gray transition hover:text-gold-dark">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 border-t border-black/5 pt-4">
                <a href="/deals" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-red transition hover:bg-red/5">🔥 Deals</a>
                <a href="/brands" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Brands</a>
                <a href="/contact" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Contact</a>
            </div>
        </div>
        <div class="border-t border-black/5 px-6 py-4">
            <a href="/account" @click="mobileMenu = false" class="btn-gold block w-full">Sign in / Register</a>
        </div>
    </div>
</nav>
