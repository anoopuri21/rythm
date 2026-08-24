@php $sec = $homeSections['deals'] ?? null; @endphp
<section id="deals" class="bg-rythme-black px-3 py-3 sm:px-5 sm:py-5">
    <article class="deals-banner relative mx-auto min-h-[650px] max-w-[1500px] overflow-hidden rounded-[2rem] text-white">
        <img src="{{ asset('images/deals-banner.jpg') }}" alt="Red electric guitar in dramatic stage lighting" width="1584" height="672" class="parallax-media absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-black/10"></div>
        <div class="relative z-10 mx-auto flex min-h-[650px] max-w-7xl items-center px-6 py-20 sm:px-12">
            <div class="reveal-section max-w-2xl">
                <p class="section-kicker text-gold-light">{{ $sec->kicker ?? 'The encore sale' }}</p>
                <h2 class="font-playfair text-5xl leading-[1.02] sm:text-7xl">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em class="text-gold-light">{{ $sec->title_accent }}</em>@endif@else Turn it up.<br><em class="text-gold-light">Prices are down.</em>@endif</h2>
                <p class="mt-6 max-w-lg text-lg leading-7 text-white/65">Save up to 35% on selected guitars, keys and studio essentials. When the timer stops, the curtain falls.</p>
                <div class="deal-countdown mt-9 flex gap-3 sm:gap-5" data-deadline-hours="72" aria-label="Sale countdown">
                    @foreach(['days'=>'Days','hours'=>'Hours','minutes'=>'Mins','seconds'=>'Secs'] as $unit => $label)
                        <div class="min-w-16 rounded-2xl border border-white/15 bg-white/10 px-3 py-4 text-center backdrop-blur-md sm:min-w-20"><span data-unit="{{ $unit }}" class="block font-bebas text-3xl text-gold-light sm:text-4xl">00</span><span class="text-[9px] font-bold uppercase tracking-widest text-white/45">{{ $label }}</span></div>
                    @endforeach
                </div>
                <a href="/deals" class="btn-gold btn-shine mt-9"><span class="relative z-10">Shop the sale</span><span class="relative z-10">↗</span></a>
            </div>
        </div>
        <div class="absolute right-8 top-8 z-10 hidden h-28 w-28 rotate-12 items-center justify-center rounded-full bg-black text-center font-bebas text-2xl leading-none text-white shadow-2xl sm:flex">UP TO<br><span class="text-4xl">35%</span><br>OFF</div>
    </article>
</section>
