@php $sec = $homeSections['why-rythme'] ?? null; @endphp
@php
    // ADMIN-DRIVEN: homepage_blocks (section_key=usp) — title, content=copy
    $promises = $homepage['usps']->map(fn ($b) => [
        'icon' => mb_substr((string) $b->title, 0, 1),
        'title' => $b->title,
        'copy' => $b->content,
    ])->all();
    if (empty($promises)) {
        $promises = [['icon'=>'✦','title'=>'Expertly inspected','copy'=>'Every instrument passes a detailed 35-point quality check before dispatch.']];
    }
@endphp

<section id="why-rythme" class="why-section bg-rythme-cream py-24 sm:py-32">
    <div class="mx-auto grid max-w-7xl gap-14 px-5 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
        {{-- Sticky media column — clean CSS sticky (no JS pinning) --}}
        <div class="relative lg:sticky lg:top-32" data-reveal="up">
            <div class="image-reveal overflow-hidden rounded-[2rem]">
                <img src="{{ asset('images/why-rythme.jpg') }}" alt="An expert carefully setting up an acoustic guitar" width="1376" height="768" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
            </div>
            <div class="absolute -bottom-6 -right-3 max-w-56 rounded-2xl bg-rythme-black p-5 text-white shadow-2xl sm:-right-8">
                <p class="font-playfair text-3xl text-gold">15+ years</p>
                <p class="mt-1 text-xs leading-5 text-white/60">helping musicians find their sound</p>
            </div>
        </div>
        <div>
            <div class="reveal-section" data-reveal="up">
                <p class="section-kicker">{{ $sec->kicker ?? 'The Rhythm Exports standard' }}</p>
                <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else More than a store.<br><em>Your partner in music.</em>@endif</h2>
                <p class="mt-6 max-w-xl leading-7 text-rythme-warm-gray">Great instruments deserve great care. From expert selection to thoughtful delivery, every detail is designed around the musician.</p>
            </div>
            <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2">
                @foreach($promises as $promise)
                    <article class="reveal-section group border-t border-black/10 pt-5" data-reveal="up">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-gold/15 text-lg text-gold-dark transition group-hover:bg-gold group-hover:text-white">{{ $promise['icon'] }}</span>
                        <h3 class="font-playfair text-xl">{{ $promise['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-rythme-warm-gray">{{ $promise['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
