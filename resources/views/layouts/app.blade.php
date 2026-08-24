<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // On-page SEO (admin-managed via seo_entries) with Blade @yield fallbacks.
        $seo = $seo ?? [];
        $seoTitle = $seo['meta_title'] ?? ($__env->yieldContent('title') ?: 'Rhythm Exports - Feel The Music, Own The Sound');
        $seoDescription = $seo['meta_description'] ?? ($__env->yieldContent('meta_description') ?: 'Rhythm Exports - Premium musical instruments, guitars, keyboards, drums, pro audio and more. Shop authentic instruments from top brands with free shipping all over India.');
        $ogTitle = $seo['og_title'] ?? ($__env->yieldContent('title') ?: 'Rhythm Exports');
        $ogDescription = $seo['og_description'] ?? $seoDescription;
        $ogImage = $seo['og_image'] ?? ($__env->yieldContent('og_image') ?: asset('images/hero-guitar.jpg'));
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if(!empty($seo['meta_keywords']))
        <meta name="keywords" content="{{ $seo['meta_keywords'] }}">
    @endif
    @if(!empty($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif

    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seo['canonical_url'] ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    @if(!empty($seo['canonical_url']))
        <link rel="canonical" href="{{ $seo['canonical_url'] }}">
    @endif
    @if(!empty($seo['schema_json']))
        <script type="application/ld+json">{!! is_array($seo['schema_json']) ? json_encode($seo['schema_json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $seo['schema_json'] !!}</script>
    @endif
    @if(!empty($seo['head_scripts']))
        {!! $seo['head_scripts'] !!}
    @endif
    <meta name="theme-color" content="#0A0A0A">
    <script>document.documentElement.classList.add('js');</script>
    <script>
        // Shared UI stores (available to all Alpine components)
        document.addEventListener('alpine:init', () => {
            Alpine.store('catDrawer', { open: false });
        });
    </script>

    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="font-inter bg-rythme-cream text-rythme-black antialiased overflow-x-clip">
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>

    @include('components.navbar')

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <button type="button" id="scroll-top" class="scroll-top" aria-label="Scroll back to top">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 15l7-7 7 7" /></svg>
    </button>

    {{-- Global footer (5-column, DB-driven) --}}
    <x-footer />

    {{-- Cart slide-over drawer (Livewire) --}}
    <livewire:cart-drawer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
