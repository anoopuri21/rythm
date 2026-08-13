@php
    // Nav taxonomy — 'Other' aur 'Deals' menu se hata diye (user spec)
    $navItems = array_values(array_filter(
        config('catalog.nav', []),
        fn ($i) => ! in_array($i['slug'], ['other', 'deals'], true)
    ));
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
@endphp

<nav id="navbar" class="sticky top-0 z-50 w-full border-b border-black/5 bg-white transition-shadow duration-300"
     x-data="{ mobileMenu: false, openMenu: null }"
     @keydown.escape.window="mobileMenu = false; openMenu = null">
    {{-- Full-width container · 30px left/right padding --}}
    <div class="w-full px-[30px]">
        {{-- ===== ROW 1 · Logo | Search | Icons ===== --}}
        <div class="flex h-16 items-center gap-3 lg:h-[4.5rem] lg:gap-6">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="nav-logo flex shrink-0 items-center gap-2.5" aria-label="{{ $brand }} home">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434"
                     class="h-8 w-auto drop-shadow-sm sm:h-9 lg:h-10"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <span class="hidden font-playfair text-xl font-bold tracking-wide text-rythme-black xl:inline-flex" aria-hidden="true">RHYTHM<span class="text-gold-dark"> EXPORTS</span></span>
            </a>

            {{-- Big search bar (center) --}}
            <form action="/shop" method="GET" role="search" class="nav-search relative mx-auto hidden w-full max-w-xl flex-1 md:block">
                <label for="nav-search" class="sr-only">Search instruments</label>
                <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-rythme-black/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input id="nav-search" type="search" name="q" placeholder="Search guitars, keyboards, mics, ukuleles…"
                       class="h-11 w-full rounded-full border border-black/10 bg-white pl-12 pr-32 text-sm text-rythme-black shadow-sm placeholder:text-black/40 outline-none transition focus:border-gold focus:ring-2 focus:ring-gold/40 lg:h-12">
                <button type="submit" class="absolute right-1.5 top-1/2 h-8 -translate-y-1/2 rounded-full bg-gold px-4 text-xs font-bold text-white transition hover:bg-gold-light sm:h-9 sm:px-5">
                    Search
                </button>
            </form>

            {{-- Icons --}}
            <div class="flex items-center gap-0.5 sm:gap-1.5">
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="nav-link relative flex h-10 w-10 items-center justify-center rounded-full text-rythme-black transition-colors duration-300 hover:bg-black/5" aria-label="Wishlist">
                    <livewire:wishlist-badge :key="'wish-badge-' . (auth()->id() ?? 'guest')" />
                </a>
                <button type="button" @click="Livewire.dispatch('cart-drawer-toggle')"
                        class="nav-link relative flex h-10 w-10 items-center justify-center rounded-full text-rythme-black transition-colors duration-300 hover:bg-black/5"
                        aria-label="Open cart">
                    <livewire:cart-badge :key="'badge-' . auth()->id() ?? 'guest'" />
                </button>
                <a href="{{ auth()->check() ? '/account' : route('login') }}" class="nav-link hidden h-10 w-10 items-center justify-center rounded-full text-rythme-black transition-colors duration-300 hover:bg-black/5 sm:flex" aria-label="Account">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </a>
                <button type="button" @click="mobileMenu = true" class="flex h-10 w-10 items-center justify-center rounded-full text-rythme-black transition-colors duration-300 hover:bg-black/5 lg:hidden" aria-label="Open navigation menu" aria-controls="mobile-menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>

        {{-- ===== ROW 2 · Shop-by-category drawer + main categories with dropdowns (desktop) ===== --}}
        <div class="hidden h-12 items-center justify-between border-t border-black/10 lg:flex">
            <button type="button"
                    @click="$store.catDrawer.open = true"
                    class="mr-6 inline-flex h-8 shrink-0 items-center gap-2 rounded-full bg-rythme-red px-5 text-[13px] font-bold uppercase tracking-[0.08em] text-white transition hover:bg-rythme-red-dark"
                    aria-haspopup="dialog" aria-controls="category-drawer"
                    :aria-expanded="$store.catDrawer.open ? 'true' : 'false'">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                All Categories
            </button>

            @foreach($navItems as $index => $item)
                <div class="relative h-full"
                     @mouseenter="openMenu = {{ $index }}"
                     @mouseleave="openMenu = null">
                    <a href="/category/{{ $item['slug'] }}"
                       class="nav-link relative flex h-full items-center gap-1 px-2.5 text-[13px] font-semibold uppercase tracking-[0.08em] text-rythme-black transition-colors duration-300 hover:text-gold-dark xl:px-3.5 xl:text-sm"
                       :class="openMenu === {{ $index }} ? 'text-gold-dark' : ''"
                       @click="openMenu = openMenu === {{ $index }} ? null : {{ $index }}"
                       :aria-expanded="openMenu === {{ $index }} ? 'true' : 'false'" aria-haspopup="true">
                        @if(!empty($item['hot']))
                            <span class="mr-1 inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-rythme-red" aria-hidden="true"></span>
                        @endif
                        {{ $item['name'] }}
                        <svg class="h-3 w-3 opacity-70 transition-transform duration-300" :class="openMenu === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M19 9l-7 7-7-7" /></svg>
                    </a>

                    {{-- Dropdown panel --}}
                    <div x-cloak x-show="openMenu === {{ $index }}"
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute left-0 top-full z-50 mt-0 w-64 rounded-b-2xl rounded-tr-2xl border border-t-0 border-black/5 bg-white p-4 shadow-[0_30px_60px_rgba(0,0,0,0.18)]"
                         @click.outside="openMenu = null">
                        <p class="mb-2.5 px-2 text-[10px] font-bold uppercase tracking-[0.22em] text-rythme-warm-gray">{{ $item['name'] }}</p>
                        <ul class="space-y-0.5">
                            @foreach($item['children'] as $child)
                                <li>
                                    <a href="/category/{{ $child['slug'] }}" @click="openMenu = null"
                                       class="block rounded-lg px-2.5 py-2 text-sm text-rythme-warm-gray transition hover:bg-gold/10 hover:text-rythme-black">
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="/category/{{ $item['slug'] }}" @click="openMenu = null" class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-rythme-black px-4 py-2 text-[11px] font-bold text-white transition hover:bg-gold hover:text-white">
                            View all <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== MOBILE DRAWER (left off-canvas) ===== --}}
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
        {{-- Mobile search --}}
        <div class="border-b border-black/5 px-4 py-3">
            <form action="/shop" method="GET" role="search" class="relative">
                <label for="nav-search-mobile" class="sr-only">Search instruments</label>
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-rythme-warm-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input id="nav-search-mobile" type="search" name="q" placeholder="Search instruments…" class="h-11 w-full rounded-full border border-black/10 bg-rythme-cream pl-11 pr-4 text-sm text-rythme-black outline-none transition focus:border-gold focus:ring-2 focus:ring-gold/30">
            </form>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-5">
            <button type="button"
                    @click="mobileMenu = false; $store.catDrawer.open = true"
                    class="mb-2 flex w-full items-center gap-2.5 rounded-xl bg-rythme-red px-4 py-3 text-sm font-bold text-white transition hover:bg-rythme-red-dark">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                Shop by Category
            </button>
            <a href="{{ route('home') }}" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Home</a>
            <a href="/shop" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Shop All</a>

            <div class="mt-2" x-data="{ openCat: null }">
                <p class="px-4 pb-2 pt-4 text-[10px] font-bold uppercase tracking-[0.25em] text-rythme-warm-gray">Categories</p>
                @foreach($navItems as $index => $item)
                    <div class="mb-1">
                        <button type="button" @click="openCat = openCat === {{ $index }} ? null : {{ $index }}"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10"
                                :aria-expanded="openCat === {{ $index }} ? 'true' : 'false'">
                            @if(!empty($item['hot']))<span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-rythme-red" aria-hidden="true"></span>@endif
                            {{ $item['name'] }}
                            <svg class="h-4 w-4 text-gold-dark transition-transform duration-300" :class="openCat === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-cloak x-show="openCat === {{ $index }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-4 border-l border-gold/30 pl-4">
                            @foreach($item['children'] as $child)
                                <a href="/category/{{ $child['slug'] }}" @click="mobileMenu = false" class="block rounded-lg px-3 py-2 text-sm text-rythme-warm-gray transition hover:text-gold-dark">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 border-t border-black/5 pt-4">
                <a href="/brands" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Brands</a>
                <a href="/contact" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-rythme-black transition hover:bg-gold/10">Contact</a>
            </div>
        </div>
        <div class="border-t border-black/5 px-6 py-4">
            @auth
                <p class="mb-2 truncate px-2 text-xs font-semibold text-rythme-warm-gray">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-gold block w-full">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="mobileMenu = false" class="btn-gold block w-full">Sign in / Register</a>
            @endauth
        </div>
    </div>

    {{-- Amazon-style shop-by-category drawer (DB-driven categories) --}}
    <x-category-drawer :categories="$navCategories" />
</nav>
