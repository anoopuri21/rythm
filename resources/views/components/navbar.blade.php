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

<nav id="navbar" class="fixed top-0 left-0 z-50 w-full navbar-transparent transition-all duration-500"
     x-data="{ mobileMenu: false, searchOpen: false, categoryDropdown: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('home') }}" class="nav-logo text-white transition-colors duration-300" aria-label="Rythme Music Store home">
                <span class="font-playfair text-2xl font-bold tracking-wider">RYTHME</span>
                <span class="hidden sm:inline-block text-xs font-inter tracking-widest ml-2 opacity-70">MUSIC STORE</span>
            </a>

            <div class="hidden lg:flex items-center space-x-8">
                @foreach ([['Home', '/'], ['Shop', '/shop']] as [$label, $url])
                    <a href="{{ $url }}" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                        {{ $label }}
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                @endforeach

                <div class="relative" @mouseenter="categoryDropdown = true" @mouseleave="categoryDropdown = false">
                    <button type="button" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold flex items-center gap-1"
                            @click="categoryDropdown = !categoryDropdown" :aria-expanded="categoryDropdown">
                        Categories
                        <svg class="w-4 h-4 transition-transform duration-300" :class="categoryDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="categoryDropdown" @click.outside="categoryDropdown = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-[600px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 grid grid-cols-2 gap-4">
                        @foreach($navCategories as $category)
                            <a href="/category/{{ Str::slug($category['name']) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-rythme-cream transition-colors duration-200 group">
                                <span class="text-2xl" aria-hidden="true">{{ $category['icon'] }}</span>
                                <span>
                                    <span class="block font-inter font-semibold text-sm text-rythme-black group-hover:text-gold transition-colors">{{ $category['name'] }}</span>
                                    <span class="block font-inter text-xs text-rythme-warm-gray">{{ $category['desc'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @foreach ([['Brands', '/brands'], ['Deals', '/deals'], ['Contact', '/contact']] as [$label, $url])
                    <a href="{{ $url }}" class="nav-link text-white transition-colors duration-300 font-inter text-sm font-medium hover:text-gold relative group">
                        {{ $label }}
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                @endforeach
            </div>

            <div class="flex items-center space-x-2 sm:space-x-4">
                <div class="relative">
                    <button type="button" @click="searchOpen = !searchOpen" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2" aria-label="Search" :aria-expanded="searchOpen">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                    <div x-cloak x-show="searchOpen" @click.outside="searchOpen = false"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-lg p-3">
                        <label for="nav-search" class="sr-only">Search instruments</label>
                        <input id="nav-search" type="search" placeholder="Search instruments..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm text-rythme-black font-inter focus:border-gold focus:ring-gold">
                    </div>
                </div>

                <a href="/wishlist" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 relative hidden sm:block" aria-label="Wishlist, 0 items">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    <span class="absolute -top-1 -right-1 bg-rythme-red text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold">0</span>
                </a>
                <a href="/cart" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 relative" aria-label="Cart, 0 items">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="absolute -top-1 -right-1 bg-rythme-red text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold">0</span>
                </a>
                <a href="/account" class="nav-link text-white transition-colors duration-300 hover:text-gold p-2 hidden sm:block" aria-label="Account">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </a>
                <button type="button" @click="mobileMenu = !mobileMenu" class="lg:hidden nav-link text-white transition-colors duration-300 hover:text-gold p-2" aria-label="Toggle navigation" :aria-expanded="mobileMenu">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-cloak x-show="mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="mobileMenu" @click.outside="mobileMenu = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         class="lg:hidden bg-white shadow-2xl rounded-b-2xl">
        <div class="px-6 py-8 space-y-3 max-h-[calc(100vh-5rem)] overflow-y-auto">
            <a href="/" class="block font-medium text-rythme-black hover:text-gold py-2">Home</a>
            <a href="/shop" class="block font-medium text-rythme-black hover:text-gold py-2">Shop</a>
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center justify-between w-full font-medium text-rythme-black hover:text-gold py-2" :aria-expanded="open">
                    Categories
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="open" class="pl-4 grid grid-cols-1 sm:grid-cols-2 gap-1 mt-2">
                    @foreach($navCategories as $category)
                        <a href="/category/{{ Str::slug($category['name']) }}" class="block text-sm text-rythme-warm-gray hover:text-gold py-1">{{ $category['icon'] }} {{ $category['name'] }}</a>
                    @endforeach
                </div>
            </div>
            <a href="/brands" class="block font-medium text-rythme-black hover:text-gold py-2">Brands</a>
            <a href="/deals" class="block font-medium text-rythme-black hover:text-gold py-2">Deals</a>
            <a href="/contact" class="block font-medium text-rythme-black hover:text-gold py-2">Contact</a>
            <hr class="border-gray-200">
            <div class="flex items-center space-x-6 pt-2">
                <a href="/wishlist" class="text-rythme-black hover:text-gold text-sm">♡ Wishlist</a>
                <a href="/account" class="text-rythme-black hover:text-gold text-sm">♙ Account</a>
            </div>
        </div>
    </div>
</nav>
