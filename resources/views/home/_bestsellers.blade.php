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

<section id="bestsellers" class="relative overflow-hidden bg-rythme-black py-24 text-white sm:py-32" x-data="{ tab: 'all' }">
    <div class="pointer-events-none absolute -right-40 top-0 h-96 w-96 rounded-full bg-rythme-red/10 blur-[120px]"></div>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-8 lg:flex-row lg:items-end">
            <div><p class="section-kicker">{{ $sec->kicker ?? 'Played. Loved. Recommended.' }}</p><h2 class="section-title text-white">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else The sound everyone is <em>talking about.</em>@endif</h2></div>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter best sellers">
                @foreach($tabs as $value => $label)
                    <button type="button" @click="tab = '{{ $value }}'" :class="tab === '{{ $value }}' ? 'bg-gold text-white border-gold' : 'border-white/15 text-white/60 hover:text-white'" class="rounded-full border px-5 py-2.5 text-xs font-bold transition">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($products as $product)
                <div x-show="tab === 'all' || tab === '{{ $product['category'] }}'" x-transition.opacity>
                    <x-product-card :product="$product" dark />
                </div>
            @endforeach
        </div>
        <div class="mt-12 text-center"><a href="/shop?sort=bestselling" class="btn-ghost-light">Shop all best sellers <span aria-hidden="true">→</span></a></div>
    </div>
</section>
