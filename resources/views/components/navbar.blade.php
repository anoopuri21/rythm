@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
@endphp

{{-- ============================================================
     NAVBAR — mega-market style (2-row header)
     Row 1 (paper): logo | pill search + submit btn | help phone | icons
     Row 2 (paper-dark): All Categories dropdown | menu | currency · sale pill
     Sticky with shadow on scroll. Mobile (≤1024px): single row —
     burger + logo + search toggle + cart; off-canvas drawer with
     Menu / Categories tabs; collapsible search bar.
     ============================================================ --}}
<header id="navbar" class="nav" x-data="{ mobileMenu: false, mobileSearch: false, mobileTab: 'menu' }"
        @keydown.escape.window="if (mobileMenu) { mobileMenu = false; $nextTick(() => $refs.mobileMenuTrigger.focus()) }">

    {{-- ===== ROW 1 · Logo | Search | Help | Icons ===== --}}
    <div class="nav__row1-wrap">
        <div class="nav__inner nav__row1">
            <button x-ref="mobileMenuTrigger" class="nav__burger" type="button" aria-label="Open menu"
                    :aria-expanded="mobileMenu" aria-controls="mobile-menu"
                    @click="mobileMenu = true; $nextTick(() => $refs.mobileMenuClose.focus())">
                <span></span><span></span><span></span>
            </button>

            <a href="{{ route('home') }}" class="nav__logo" aria-label="{{ $brand }} home">
                <img src="{{ \Illuminate\Support\Facades\URL::to($logo) }}" alt="{{ $brand }} logo" width="1466" height="434"
                     class="nav__logo-img" onerror="this.onerror=null;this.src='{{ asset('images/logo-rythme.svg') }}';">
                <span class="nav__logo-text" style="display:none">RHYTHM <em>EXPORTS</em></span>
            </a>

            {{-- Center pill search (desktop) --}}
            <form action="/shop" method="GET" class="nav__search" role="search">
                <label for="nav-search" class="sr-only">Search instruments</label>
                <input id="nav-search" type="search" name="q" placeholder="Search in {{ $brand }}…" aria-label="Search instruments">
                <button type="submit" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                    <span>Search</span>
                </button>
            </form>

            {{-- Help / phone (desktop only) --}}
            <a href="{{ url('/contact') }}" class="nav__help">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0 4.556 4.365 8.25 9.75 8.25a11.2 11.2 0 0 0 4.683-.992l3.817.992-1.105-3.276A7.44 7.44 0 0 0 21.75 12c0-4.556-4.365-8.25-9.75-8.25S2.25 7.444 2.25 12Z"/></svg>
                <span class="nav__help-text">
                    <em>Questions about an instrument?</em>
                    <strong>Contact our team</strong>
                </span>
            </a>

            <div class="nav__icons">
                {{-- Mobile search toggle --}}
                <button type="button" class="nav__icon nav__icon--search" aria-label="Toggle search"
                        @click="mobileSearch = !mobileSearch">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                </button>
                <a href="{{ auth()->check() ? route('account.index') : route('login') }}" class="nav__icon nav__icon--profile" aria-label="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                    <span class="nav__icon-label">Account</span>
                </a>
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="nav__icon nav__icon--wishlist" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0z"/></svg>
                    <livewire:wishlist-badge :key="'wish-' . (auth()->id() ?? 'guest')" />
                    <span class="nav__icon-label">Wishlist</span>
                </a>
                <button type="button" class="nav__icon nav__icon--cart" aria-label="Open cart" @click="Livewire.dispatch('cart-drawer-toggle')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <livewire:cart-badge :key="'badge-' . (auth()->id() ?? 'guest')" />
                    <span class="nav__icon-label">My Cart</span>
                </button>
            </div>
        </div>

        {{-- Mobile collapsible search --}}
        <div class="nav__msearch" x-cloak x-show="mobileSearch" x-collapse.duration.250ms>
            <form action="/shop" method="GET" role="search">
                <label for="nav-search-m" class="sr-only">Search instruments</label>
                <input id="nav-search-m" type="search" name="q" placeholder="Search in {{ $brand }}…">
                <button type="submit" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- ===== ROW 2 · Categories | Menu | Meta (desktop) ===== --}}
    <div class="nav__row2-wrap">
        <div class="nav__inner nav__row2">

            {{-- All Categories dropdown --}}
            <div class="nav__cats" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape="open = false">
                <button type="button" class="nav__cats-btn" :aria-expanded="open" aria-haspopup="true"
                        @click="open = !open">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    All Categories
                    <svg class="nav__cats-chev" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div class="nav__cats-panel" x-cloak x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1.5"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <ul>
                        @foreach($navCategories as $cat)
                            <li class="nav__cats-item">
                                <a href="/shop?category={{ $cat['slug'] }}">
                                    {{ $cat['name'] }}
                                    @if(count($cat['children']))
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    @endif
                                </a>
                                @if(count($cat['children']))
                                    <div class="nav__cats-fly">
                                        <p>{{ $cat['name'] }}</p>
                                        <ul>
                                            @foreach($cat['children'] as $child)
                                                <li><a href="/shop?category={{ $child['slug'] }}">{{ $child['name'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                        <li class="nav__cats-all"><a href="/shop">View all products →</a></li>
                    </ul>
                </div>
            </div>

            {{-- Main menu --}}
            <nav class="nav__menu" aria-label="Main navigation">
                <a href="{{ route('home') }}" class="nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}">Home</a>
                <a href="/shop" class="nav__link">Shop</a>
                <a href="/shop?on_sale=1" class="nav__link">Deals</a>
                <a href="/about" class="nav__link">Our Story</a>
                <a href="/contact" class="nav__link">Contact</a>
            </nav>

            {{-- Right meta: currency · sale pill --}}
            <div class="nav__meta">
                <div class="nav__meta-dd" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" :aria-expanded="open" @click="open = !open">
                        INR ₹
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <ul x-cloak x-show="open" x-transition.origin.top>
                        <li><button type="button" @click="open = false">INR ₹</button></li>
                        <li><button type="button" @click="open = false">USD $</button></li>
                    </ul>
                </div>
                <a href="/shop?on_sale=1" class="nav__sale">Browse current offers</a>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE DRAWER (left off-canvas, Menu/Categories tabs) ===== --}}
    <div x-cloak x-show="mobileMenu" x-transition.opacity.duration.250ms class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm lg:hidden"
         @click="mobileMenu = false; $nextTick(() => $refs.mobileMenuTrigger.focus())" aria-hidden="true"></div>
    <aside id="mobile-menu" x-cloak x-show="mobileMenu" x-trap.inert.noscroll="mobileMenu"
           x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           role="dialog" aria-modal="true" aria-label="Mobile navigation"
           class="drawer fixed inset-y-0 left-0 z-[70] flex w-[86%] max-w-sm flex-col bg-paper shadow-2xl lg:hidden">
        <div class="drawer__head">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" @click="mobileMenu = false">
                <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-8 w-auto"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                <span class="drawer__logo-text" style="display:none">RHYTHM <em>EXPORTS</em></span>
            </a>
            <button x-ref="mobileMenuClose" class="drawer__close" type="button" aria-label="Close menu"
                    @click="mobileMenu = false; $nextTick(() => $refs.mobileMenuTrigger.focus())">&times;</button>
        </div>

        {{-- Tabs: Menu | Categories --}}
        <div class="drawer__tabs" role="tablist" aria-label="Drawer sections">
            <button id="drawer-menu-tab" type="button" role="tab" aria-controls="drawer-menu-panel"
                    :tabindex="mobileTab === 'menu' ? 0 : -1" :aria-selected="mobileTab === 'menu'"
                    :class="mobileTab === 'menu' && 'is-active'" @click="mobileTab = 'menu'">Menu</button>
            <button id="drawer-categories-tab" type="button" role="tab" aria-controls="drawer-categories-panel"
                    :tabindex="mobileTab === 'cats' ? 0 : -1" :aria-selected="mobileTab === 'cats'"
                    :class="mobileTab === 'cats' && 'is-active'" @click="mobileTab = 'cats'">Categories</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{-- MENU TAB --}}
            <div id="drawer-menu-panel" role="tabpanel" aria-labelledby="drawer-menu-tab"
                 x-show="mobileTab === 'menu'">
                <form action="/shop" method="GET" role="search" class="relative mb-4">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                    <label for="nav-search-mobile" class="sr-only">Search instruments</label>
                    <input id="nav-search-mobile" type="search" name="q" placeholder="Search in {{ $brand }}…"
                           class="h-11 w-full rounded-full border border-ink/10 bg-paper pl-11 pr-4 text-sm text-ink outline-none transition focus:border-ink focus:ring-2 focus:ring-ink/10">
                </form>

                <a href="{{ route('home') }}" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5">Home</a>
                <a href="/shop" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5">Shop</a>
                <a href="/shop?on_sale=1" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5">Deals</a>
                <a href="/about" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5">Our Story</a>
                <a href="/contact" @click="mobileMenu = false" class="block rounded-xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5">Contact</a>

                <div class="mt-4 border-t border-ink/10 pt-4">
                    <a href="/shop?on_sale=1" @click="mobileMenu = false" class="drawer__sale">Browse current offers</a>
                </div>
            </div>

            {{-- CATEGORIES TAB (accordion) --}}
            <div id="drawer-categories-panel" role="tabpanel" aria-labelledby="drawer-categories-tab"
                 x-show="mobileTab === 'cats'" x-cloak>
                @foreach($navCategories as $cat)
                    <div x-data="{ sub: false }" class="border-b border-ink/5">
                        <div class="flex items-center">
                            <a href="/shop?category={{ $cat['slug'] }}" @click="mobileMenu = false"
                               class="flex-1 px-4 py-3 text-sm font-semibold text-ink">{{ $cat['name'] }}</a>
                            @if(count($cat['children']))
                                <button type="button" class="p-3 text-ink/50" :aria-expanded="sub" aria-label="Toggle {{ $cat['name'] }} subcategories" @click="sub = !sub">
                                    <svg class="h-4 w-4 transition-transform" :class="sub && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            @endif
                        </div>
                        @if(count($cat['children']))
                            <div x-show="sub" x-collapse.duration.200ms x-cloak class="pb-2 pl-6">
                                @foreach($cat['children'] as $child)
                                    <a href="/shop?category={{ $child['slug'] }}" @click="mobileMenu = false"
                                       class="block px-2 py-2 text-[13px] font-medium text-muted transition hover:text-ink">{{ $child['name'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
                <a href="/shop" @click="mobileMenu = false" class="mt-3 block px-4 py-2 text-sm font-bold text-ink underline underline-offset-4">View all products →</a>
            </div>
        </div>

        <div class="border-t border-ink/5 px-6 py-4">
            @auth
                <p class="mb-2 truncate px-2 text-xs font-semibold text-ink/50">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-gold block w-full">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="mobileMenu = false" class="btn-gold block w-full">Sign in / Register</a>
            @endauth
        </div>
    </aside>

    {{-- Amazon-style shop-by-category drawer (DB-driven) — kept for other triggers --}}
    <x-category-drawer :categories="$navCategories" />
</header>
