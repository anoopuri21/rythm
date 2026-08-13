<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rhythm Exports - Feel The Music, Own The Sound')</title>
    <meta name="description" content="@yield('meta_description', 'Rhythm Exports - Premium musical instruments, guitars, keyboards, drums, pro audio and more. Shop authentic instruments from top brands with free shipping all over India.')">

    <meta property="og:title" content="@yield('title', 'Rhythm Exports')">
    <meta property="og:description" content="@yield('meta_description', 'Premium musical instruments store')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/hero-guitar.jpg'))">
    <meta name="theme-color" content="#0A0A0A">
    <script>document.documentElement.classList.add('js');</script>
    <script>
        // Shared UI stores (available to all Alpine components)
        document.addEventListener('alpine:init', () => {
            Alpine.store('catDrawer', { open: false });
        });
    </script>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="font-inter bg-rythme-cream text-rythme-black antialiased overflow-x-hidden">
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>

    @include('components.navbar')

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <button type="button" id="scroll-top" class="scroll-top" aria-label="Scroll back to top">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 15l7-7 7 7" /></svg>
    </button>

    {{-- Cart slide-over drawer (Livewire) --}}
    <livewire:cart-drawer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
