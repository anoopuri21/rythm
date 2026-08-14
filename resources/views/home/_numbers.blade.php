@php $sec = $homeSections['numbers'] ?? null; @endphp
@php
    $stats = [
        ['value'=>15,'suffix'=>'+','label'=>'Years of expertise'],
        ['value'=>12000,'suffix'=>'+','label'=>'Musicians served'],
        ['value'=>50,'suffix'=>'+','label'=>'World-class brands'],
        ['value'=>4.9,'suffix'=>'/5','label'=>'Average customer rating','decimals'=>1],
    ];
@endphp

<section id="numbers" class="numbers-section parallax-section relative overflow-hidden bg-rythme-black py-28 text-white sm:py-36" style="background-image: linear-gradient(rgba(10,10,10,.88), rgba(10,10,10,.94)), url('{{ asset('images/hero-studio.jpg') }}')">
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mx-auto mb-16 max-w-2xl text-center"><p class="section-kicker justify-center">{{ $sec->kicker ?? 'In tune with India' }}</p><h2 class="font-playfair text-4xl sm:text-5xl">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em class="text-gold">{{ $sec->title_accent }}</em>@endif@else A community that keeps <em class="text-gold">growing.</em>@endif</h2></div>
        <div class="grid grid-cols-2 gap-y-12 lg:grid-cols-4">
            @foreach($stats as $stat)
                <div class="reveal-section relative text-center lg:border-r lg:border-white/10 lg:last:border-0">
                    <p class="font-bebas text-5xl tracking-wide text-gold-light sm:text-7xl"><span class="stat-counter" data-count="{{ $stat['value'] }}" data-decimals="{{ $stat['decimals'] ?? 0 }}">0</span>{{ $stat['suffix'] }}</p>
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-16 text-center font-playfair text-xl italic text-white/45">“Music gives a soul to the universe and wings to the mind.”</p>
    </div>
</section>
