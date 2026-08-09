@php
    $products = [
        ['brand'=>'Fender','name'=>'Player II Stratocaster Electric Guitar','price'=>'79,999','old_price'=>'86,400','reviews'=>128,'badge'=>'Bestseller','image'=>'images/product-guitar.jpg','category'=>'guitars'],
        ['brand'=>'Yamaha','name'=>'PSR-E473 Portable Keyboard','price'=>'32,490','old_price'=>'36,990','reviews'=>94,'badge'=>'Sale','image'=>'images/product-keyboard.jpg','category'=>'keys'],
        ['brand'=>'Gibson','name'=>'Les Paul Studio Electric Guitar','price'=>'1,64,999','reviews'=>47,'badge'=>'Icon','image'=>'images/product-guitar.jpg','category'=>'guitars'],
        ['brand'=>'Roland','name'=>'JUNO-DS61 Performance Synthesizer','price'=>'74,500','old_price'=>'81,250','reviews'=>71,'badge'=>'Bestseller','image'=>'images/product-keyboard.jpg','category'=>'keys'],
    ];
@endphp

<section id="bestsellers" class="relative overflow-hidden bg-rythme-black py-24 text-white sm:py-32" x-data="{ tab: 'all' }">
    <div class="pointer-events-none absolute -right-40 top-0 h-96 w-96 rounded-full bg-rythme-red/10 blur-[120px]"></div>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-8 lg:flex-row lg:items-end">
            <div><p class="section-kicker">Played. Loved. Recommended.</p><h2 class="section-title text-white">The sound everyone is <em>talking about.</em></h2></div>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter best sellers">
                @foreach(['all'=>'All hits','guitars'=>'Guitars','keys'=>'Keys'] as $value => $label)
                    <button type="button" @click="tab = '{{ $value }}'" :class="tab === '{{ $value }}' ? 'bg-gold text-rythme-black border-gold' : 'border-white/15 text-white/60 hover:text-white'" class="rounded-full border px-5 py-2.5 text-xs font-bold transition">{{ $label }}</button>
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
        <div class="mt-12 text-center"><a href="/shop?sort=bestselling" class="inline-flex items-center gap-3 rounded-full border border-white/20 px-7 py-3 text-sm font-semibold transition hover:border-gold hover:text-gold">Shop all best sellers <span>→</span></a></div>
    </div>
</section>
