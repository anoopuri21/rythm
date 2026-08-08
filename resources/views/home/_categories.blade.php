@php
    $categories = [
        ['name' => 'Guitars', 'icon' => '🎸', 'count' => '480+ instruments', 'tone' => 'from-[#7c2d12] to-[#1c1917]', 'wide' => true],
        ['name' => 'Keys & Pianos', 'icon' => '🎹', 'count' => '210+ instruments', 'tone' => 'from-[#1e293b] to-[#020617]'],
        ['name' => 'Drums', 'icon' => '🥁', 'count' => '190+ instruments', 'tone' => 'from-[#7f1d1d] to-[#1c1917]'],
        ['name' => 'Pro Audio', 'icon' => '🎙️', 'count' => '350+ essentials', 'tone' => 'from-[#334155] to-[#0f172a]'],
        ['name' => 'Live Sound', 'icon' => '🔊', 'count' => '160+ systems', 'tone' => 'from-[#713f12] to-[#1c1917]', 'wide' => true],
        ['name' => 'Wind', 'icon' => '🎷', 'count' => '120+ instruments', 'tone' => 'from-[#92400e] to-[#292524]'],
        ['name' => 'Indian', 'icon' => '🪕', 'count' => '140+ instruments', 'tone' => 'from-[#9f1239] to-[#292524]'],
        ['name' => 'Accessories', 'icon' => '🎵', 'count' => '900+ essentials', 'tone' => 'from-[#3f3f46] to-[#18181b]'],
    ];
@endphp

<section id="categories" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    <span class="music-note left-[5%] top-20">♪</span><span class="music-note right-[8%] top-40">♫</span>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <div>
                <p class="section-kicker">Find your voice</p>
                <h2 class="section-title">Instruments for every <em>kind of artist.</em></h2>
            </div>
            <a href="/shop" class="text-link shrink-0">View all categories <span>↗</span></a>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $category)
                <a href="/category/{{ Str::slug($category['name']) }}" class="category-card group relative min-h-64 overflow-hidden rounded-3xl bg-gradient-to-br {{ $category['tone'] }} p-7 text-white {{ ($category['wide'] ?? false) ? 'lg:col-span-2' : '' }}">
                    <div class="absolute -bottom-12 -right-8 text-[9rem] opacity-20 grayscale transition duration-700 group-hover:scale-110 group-hover:rotate-6 group-hover:opacity-35" aria-hidden="true">{{ $category['icon'] }}</div>
                    <div class="relative z-10 flex h-full flex-col justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-xl backdrop-blur-sm">{{ $category['icon'] }}</span>
                        <div>
                            <p class="mb-1 text-xs uppercase tracking-[0.2em] text-white/50">{{ $category['count'] }}</p>
                            <h3 class="font-playfair text-2xl sm:text-3xl">{{ $category['name'] }}</h3>
                            <span class="mt-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/25 transition duration-300 group-hover:translate-x-2 group-hover:border-gold group-hover:text-gold">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
