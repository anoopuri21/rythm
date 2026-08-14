@php $sec = $homeSections['new-arrivals'] ?? null; @endphp
@php
    $featured = config('catalog.featured');
    $editorPick = $featured[0]; // Squier Sonic Stratocaster
    $arrivals = [$featured[7], $featured[5], $featured[6]]; // Casio PX-S1100, DT-770, Ibanez GRG170DX
@endphp

<section id="new-arrivals" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    @include('components.instrument-decor')
    <div class="relative z-[1] mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="section-kicker">{{ $sec->kicker ?? 'Fresh from the flight case' }}</p><h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Meet the <em>new arrivals.</em>@endif</h2></div><a href="/shop?sort=newest" class="text-link">See everything new <span>↗</span></a></div>
        <div class="grid gap-5 lg:grid-cols-2">
            <article class="reveal-section group relative min-h-[620px] overflow-hidden rounded-[2rem] bg-rythme-black text-white">
                {{-- Image: Bajaao real product photo --}}
                <img src="{{ $editorPick['image'] }}" alt="{{ $editorPick['name'] }} — real product photo from Bajaao" width="1024" height="1024" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/15 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-8 sm:p-10">
                    <span class="rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Editor's pick</span>
                    <p class="mt-5 text-xs uppercase tracking-[0.2em] text-gold-light">{{ $editorPick['brand'] }}</p>
                    <h3 class="mt-2 max-w-md font-playfair text-3xl sm:text-4xl">{{ $editorPick['name'] }}</h3>
                    <div class="mt-6 flex items-center justify-between"><p class="text-xl font-bold">₹{{ number_format($editorPick['price']) }}</p><a href="/product/{{ Str::slug($editorPick['name']) }}" class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-black transition group-hover:bg-gold" aria-label="View {{ $editorPick['name'] }}">↗</a></div>
                </div>
            </article>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach($arrivals as $product)<div class="reveal-section"><x-product-card :product="$product" /></div>@endforeach
                <a href="/shop?sort=newest" class="reveal-section group col-span-full flex min-h-44 items-center justify-between overflow-hidden rounded-3xl bg-gold p-8 text-white">
                    <div><p class="text-xs font-bold uppercase tracking-[0.2em]">Updated every week</p><h3 class="mt-2 font-playfair text-3xl">Discover what just landed.</h3></div><span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-rythme-black text-xl text-white transition group-hover:rotate-45">↗</span>
                </a>
            </div>
        </div>
    </div>
</section>
