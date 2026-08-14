@php $sec = $homeSections['bestsellers'] ?? null; @endphp
@php
    $tabs = [
        'all' => 'All hits',
        'guitars' => 'Guitars',
        'keys' => 'Keys',
        'drums' => 'Drums',
        'pro-audio' => 'Pro Audio',
    ];
    $categoryByBrand = [
        'Fender' => 'guitars', 'Ibanez' => 'guitars', 'Casio' => 'keys', 'Roland' => 'keys',
        'Alesis' => 'drums', 'Focusrite' => 'pro-audio', 'Shure' => 'pro-audio', 'Beyerdynamic' => 'pro-audio',
    ];
    $products = array_map(fn ($p) => $p + ['category' => $categoryByBrand[$p['brand']] ?? 'all'], config('catalog.featured'));
@endphp

{{-- ============================================================
     BEST SELLERS — "Discover Top Picks" (reference minimal-tech)
     Light gray section · white cards · minimal underline tabs
     ============================================================ --}}
<section id="bestsellers" class="bg-rythme-cream-dark py-20 sm:py-28" x-data="{ tab: 'all' }">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div>
                <p class="section-kicker">{{ $sec->kicker ?? 'Played. Loved. Recommended.' }}</p>
                <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else The sound everyone is <em>talking about.</em>@endif</h2>
                <p class="mt-4 max-w-xl text-sm leading-6 text-rythme-warm-gray">
                    Top-selling instruments, trusted by players for quality and performance.
                </p>
            </div>
            <div class="flex flex-wrap gap-6" role="tablist" aria-label="Filter best sellers">
                @foreach($tabs as $value => $label)
                    <button type="button" @click="tab = '{{ $value }}'"
                            :class="tab === '{{ $value }}' ? 'border-black text-black' : 'border-transparent text-rythme-warm-gray hover:text-black'"
                            class="border-b-2 pb-1.5 text-[13px] font-semibold transition">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($products as $product)
                <div x-show="tab === 'all' || tab === '{{ $product['category'] }}'" x-transition.opacity>
                    <x-minimal-product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="/shop?sort=bestselling" class="text-link">Shop all best sellers <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>
