@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
@endphp

{{-- ============================================================
     NAVBAR — design-prototype style (ported + responsive)
     Sticky · white blur · row1: logo | pill search | icons
     row2: centered uppercase menu with underline hover
     Mobile (≤900px): burger + drawer (menu/search hidden)
     ============================================================ --}}
<header id="navbar" class="nav" x-data="{ mobileMenu: false }"
        @keydown.escape.window="mobileMenu = false">
    <div class="nav__inner">
        {{-- ===== ROW 1 · Logo | Search | Icons ===== --}}
        <div class="nav__row1">
            <a href="{{ route('home') }}" class="nav__logo" aria-label="{{ $brand }} home">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434"
                     class="nav__logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                <span class="nav__logo-text" style="display:none">RHYTHM <em>EXPORTS</em></span>
            </a>

            {{-- Centered pill search --}}
            <form action="/shop" method="GET" class="nav__search" role="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                <label for="nav-search" class="sr-only">Search instruments</label>
                <input id="nav-search" type="search" name="q" placeholder="Search guitars, keyboards, mics, ukuleles…" aria-label="Search instruments">
                <button type="submit">Search</button>
            </form>

            <div class="nav__icons">
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="nav__icon" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0z"/></svg>
                    <livewire:wishlist-badge :key="'wish-' . (auth()->id() ?? 'guest')" />
                </a>
                <button type="button" class="nav__icon" aria-label="Open cart" @click="Livewire.dispatch('cart-drawer-toggle')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <livewire:cart-badge :key="'badge-' . (auth()->id() ?? 'guest')" />
                </button>
                <a href="{{ auth()->check() ? route('account.index') : route('login') }}" class="nav__icon nav__icon--profile" aria-label="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                </a>
                <button class="nav__burger" type="button" aria-label="Open menu" aria-expanded="false"
                        aria-controls="mobile-menu" @click="mobileMenu = true">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        {{-- ===== ROW 2 · Centered menu (desktop) ===== --}}
        <nav class="nav__menu" aria-label="Main navigation">
            <a href="/shop" class="nav__link">Shop</a>
            <a href="/about" class="nav__link">About</a>
            <a href="/shop" class="nav__link">Best Sellers</a>
            <a href="/contact" class="nav__link">Contact</a>
        </nav>
    </div>

    {{-- ===== MOBILE DRAWER (left off-canvas) ===== --}}
    <div x-cloak x-show="mobileMenu" x-transition.opacity.duration.250ms class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm lg:hidden" @click="mobileMenu = false" aria-hidden="true"></div>
    <aside id="mobile-menu" x-cloak x-show="mobileMenu"
           x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           role="dialog" aria-modal="true" aria-label="Mobile navigation"
           class="drawer fixed inset-y-0 left-0 z-[70] flex w-[86%] max-w-sm flex-col bg-white shadow-2xl">
        <div class="drawer__head">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" @click="mobileMenu = false">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-8 w-auto"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                <span class="drawer__logo-text" style="display:none">RHYTHM <em>EXPORTS</em></span>
            </a>
            <button class="drawer__close" type="button" aria-label="Close menu" @click="mobileMenu = false">&times;</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{-- Mobile search --}}
            <form action="/shop" method="GET" role="search" class="relative mb-4">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                <label for="nav-search-mobile" class="sr-only">Search instruments</label>
                <input id="nav-search-mobile" type="search" name="q" placeholder="Search instruments…"
                       class="h-11 w-full rounded-full border border-black/10 bg-white pl-11 pr-4 text-sm text-black outline-none transition focus:border-black focus:ring-2 focus:ring-black/10">
            </form>

            <a href="/shop" @click="mobileMenu = false"
               class="mb-1 flex w-full items-center gap-2.5 rounded-xl border border-black/15 px-4 py-3 text-sm font-bold text-black transition hover:border-black">
                Shop by Category
            </a>
            <a href="{{ route('home') }}" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-black transition hover:bg-black/5">Home</a>
            <a href="/shop" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-black transition hover:bg-black/5">Shop All</a>
            <a href="/about" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-black transition hover:bg-black/5">About</a>
            <a href="/contact" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-black transition hover:bg-black/5">Contact</a>
        </div>

        <div class="border-t border-black/5 px-6 py-4">
            @auth
                <p class="mb-2 truncate px-2 text-xs font-semibold text-black/50">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-gold block w-full">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="mobileMenu = false" class="btn-gold block w-full">Sign in / Register</a>
            @endauth
        </div>
    </aside>

</header>
