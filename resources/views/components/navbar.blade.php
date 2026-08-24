@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
@endphp

{{-- ============================================================
     NAVBAR — mega-market style (2-row header)
     Row 1 (paper): logo | pill search + submit btn | help phone | icons
     Row 2 (paper-dark): All Categories dropdown | menu | lang·currency | sale pill
     Sticky with shadow on scroll. Mobile (≤1024px): single row —
     burger + logo + search toggle + cart; off-canvas drawer with
     Menu / Categories tabs; collapsible search bar.
     ============================================================ --}}
<header id="navbar" class="nav" x-data="{ mobileMenu: false, mobileSearch: false, mobileTab: 'menu' }"
        @keydown.escape.window="mobileMenu = false">

    {{-- ===== ROW 1 · Logo | Search | Help | Icons ===== --}}
    <div class="nav__row1-wrap">
        <div class="nav__inner nav__row1">
            <button class="nav__burger" type="button" aria-label="Open menu" aria-expanded="false"
                    aria-controls="mobile-menu" @click="mobileMenu = true">
                <span></span><span></span><span></span>
            </button>

            <a href="{{ route('home') }}" class="nav__logo" aria-label="{{ $brand }} home">
                <img src="{{ \Illuminate\Support\Facades\URL::to($logo) }}" alt="{{ $brand }} logo" width="1466" height="434"
                     class="nav__logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
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
            <a href="tel:+919876543210" class="nav__help">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                <span class="nav__help-text">
                    <em>Need help? Call us</em>
                    <strong>+91 98765 43210</strong>
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
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="nav__icon" aria-label="Wishlist">
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

            {{-- Right meta: language · currency · sale pill --}}
            <div class="nav__meta">
                <div class="nav__meta-dd" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" :aria-expanded="open" @click="open = !open">
                        English
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <ul x-cloak x-show="open" x-transition.origin.top>
                        <li><button type="button" @click="open = false">English</button></li>
                        <li><button type="button" @click="open = false">हिन्दी</button></li>
                    </ul>
                </div>
                <span class="nav__meta-sep" aria-hidden="true"></span>
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
                <a href="/shop?on_sale=1" class="nav__sale">Sale! Up to 30% Off</a>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE DRAWER (left off-canvas, Menu/Categories tabs) ===== --}}
    <div x-cloak x-show="mobileMenu" x-transition.opacity.duration.250ms class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm lg:hidden" @click="mobileMenu = false" aria-hidden="true"></div>
    <aside id="mobile-menu" x-cloak x-show="mobileMenu"
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
            <button class="drawer__close" type="button" aria-label="Close menu" @click="mobileMenu = false">&times;</button>
        </div>

        {{-- Tabs: Menu | Categories --}}
        <div class="drawer__tabs" role="tablist" aria-label="Drawer sections">
            <button type="button" role="tab" :aria-selected="mobileTab === 'menu'"
                    :class="mobileTab === 'menu' && 'is-active'" @click="mobileTab = 'menu'">Menu</button>
            <button type="button" role="tab" :aria-selected="mobileTab === 'cats'"
                    :class="mobileTab === 'cats' && 'is-active'" @click="mobileTab = 'cats'">Categories</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{-- MENU TAB --}}
            <div x-show="mobileTab === 'menu'">
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
                    <a href="/shop?on_sale=1" @click="mobileMenu = false" class="drawer__sale">Sale! Up to 30% Off</a>
                </div>
            </div>

            {{-- CATEGORIES TAB (accordion) --}}
            <div x-show="mobileTab === 'cats'" x-cloak>
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
