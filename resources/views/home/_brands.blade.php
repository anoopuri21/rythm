@php $brands = ['FENDER','YAMAHA','GIBSON','ROLAND','CASIO','MARSHALL','IBANEZ','SHURE']; @endphp

<section id="brands" class="overflow-hidden bg-white py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mx-auto mb-14 max-w-3xl text-center"><p class="section-kicker justify-center">The names behind the music</p><h2 class="section-title">Legendary makers.<br><em>One trusted destination.</em></h2></div>
    </div>
    <div class="brand-marquee border-y border-black/10 py-8" aria-label="Featured brands">
        <div class="brand-marquee-track flex w-max items-center">
            @foreach(array_merge($brands, $brands) as $brand)
                <a href="/brands/{{ Str::slug($brand) }}" class="mx-8 font-bebas text-3xl tracking-[0.12em] text-rythme-black/35 transition hover:text-gold sm:mx-14 sm:text-4xl">{{ $brand }}</a><span class="text-gold">✦</span>
            @endforeach
        </div>
    </div>
    <div class="mx-auto mt-16 max-w-7xl px-5 sm:px-8">
        <article class="reveal-section relative min-h-[520px] overflow-hidden rounded-[2rem] bg-rythme-black text-white">
            <img src="{{ asset('images/brand-feature.jpg') }}" alt="Electric guitar and amplifier in a golden stage light" width="1376" height="768" class="parallax-media absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/70 to-transparent"></div>
            <div class="relative z-10 flex min-h-[520px] max-w-xl flex-col justify-center p-8 sm:p-14">
                <p class="mb-5 text-xs font-bold uppercase tracking-[0.28em] text-gold">Brand spotlight · Fender</p>
                <h3 class="font-playfair text-4xl leading-tight sm:text-6xl">Born in California.<br><em class="text-gold-light">Played everywhere.</em></h3>
                <p class="mt-6 max-w-md leading-7 text-white/65">From the unmistakable Stratocaster to the thunder of the Precision Bass, meet the instruments that shaped modern music.</p>
                <a href="/brands/fender" class="btn-ghost-light mt-8">Explore Fender <span>↗</span></a>
            </div>
        </article>
    </div>
</section>
