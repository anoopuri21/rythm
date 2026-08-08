@php
    $arrivals = [
        ['brand'=>'Fender','name'=>'American Professional II Stratocaster','price'=>'1,79,999','reviews'=>18,'badge'=>'Just in','image'=>'images/product-guitar.jpg'],
        ['brand'=>'Yamaha','name'=>'CK61 Stage Keyboard','price'=>'89,990','reviews'=>12,'badge'=>'New','image'=>'images/product-keyboard.jpg'],
        ['brand'=>'Ibanez','name'=>'AZ Premium Electric Guitar','price'=>'1,19,500','reviews'=>9,'badge'=>'New','image'=>'images/product-guitar.jpg'],
    ];
@endphp

<section id="new-arrivals" class="overflow-hidden bg-rythme-cream py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="section-kicker">Fresh from the flight case</p><h2 class="section-title">Meet the <em>new arrivals.</em></h2></div><a href="/shop?sort=newest" class="text-link">See everything new <span>↗</span></a></div>
        <div class="grid gap-5 lg:grid-cols-2">
            <article class="reveal-section group relative min-h-[620px] overflow-hidden rounded-[2rem] bg-rythme-black text-white">
                <img src="{{ asset('images/product-guitar.jpg') }}" alt="Fender American Professional II Stratocaster" width="1024" height="1024" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/15 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-8 sm:p-10">
                    <span class="rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-rythme-black">Editor's pick</span>
                    <p class="mt-5 text-xs uppercase tracking-[0.2em] text-gold-light">Fender</p><h3 class="mt-2 max-w-md font-playfair text-3xl sm:text-4xl">American Professional II Stratocaster</h3>
                    <div class="mt-6 flex items-center justify-between"><p class="text-xl font-bold">₹1,79,999</p><a href="/product/american-professional-ii-stratocaster" class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-black transition group-hover:bg-gold">↗</a></div>
                </div>
            </article>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach(array_slice($arrivals, 1) as $product)<div class="reveal-section"><x-product-card :product="$product" /></div>@endforeach
                <a href="/shop?sort=newest" class="reveal-section group col-span-full flex min-h-44 items-center justify-between overflow-hidden rounded-3xl bg-gold p-8 text-rythme-black">
                    <div><p class="text-xs font-bold uppercase tracking-[0.2em]">Updated every week</p><h3 class="mt-2 font-playfair text-3xl">Discover what just landed.</h3></div><span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-rythme-black text-xl text-white transition group-hover:rotate-45">↗</span>
                </a>
            </div>
        </div>
    </div>
</section>
