@php $sec = $homeSections['ugc'] ?? null; @endphp
{{--
    ============================================================
    s14 · UGC — "Made by the #RhythmExportsFamily"
    Dark gallery grid. Images: AI Generated — [AI Generated]
    (placeholder visuals; swap for real community posts later)
    ============================================================
--}}
@php
    $ugcPosts = [
        ['image' => 'images/ugc/studio-vocalist.jpg', 'handle' => '@ria.makes.music', 'caption' => 'Tracking vocals on her first Rhythm Exports studio bundle.'],
        ['image' => 'images/ugc/guitar-corner.jpg', 'handle' => '@akash.plays', 'caption' => 'Sunday mornings with the CS11 and a window seat.'],
        ['image' => 'images/ugc/dj-desk.jpg', 'handle' => '@decks.by.dev', 'caption' => 'Weekend sets on a Rhythm Exports-sourced rig.'],
    ];
@endphp

<section id="ugc" class="relative overflow-hidden bg-rythme-black py-24 text-white sm:py-32">
    <div class="pointer-events-none absolute left-1/2 top-0 h-96 w-[46rem] -translate-x-1/2 rounded-full bg-gold/5 blur-[140px]"></div>
    <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mb-14 flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end" data-reveal="up">
            <div class="max-w-2xl">
                <p class="section-kicker text-gold-light">{{ $sec->kicker ?? 'Community' }}</p>
                <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Made by the <em class="text-red-gradient">#RhythmExportsFamily.</em>@endif</h2>
                <p class="mt-5 text-base leading-7 text-white/60">
                    From first chords to full stages — tag <span class="font-semibold text-gold-light">#RhythmExportsFamily</span> to feature here and win monthly gear vouchers.
                </p>
            </div>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="btn-gold btn-shine">
                <span class="relative z-10">Share your sound</span><span class="relative z-10" aria-hidden="true">↗</span>
            </a>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            @foreach($ugcPosts as $post)
                <figure class="img-zoom-hover group relative overflow-hidden rounded-3xl" data-reveal="up">
                    {{-- Image: AI Generated — [AI Generated] --}}
                    <img
                        src="{{ asset($post['image']) }}"
                        alt="{{ $post['handle'] }} in their studio — AI Generated"
                        class="aspect-[4/5] w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        width="800"
                        height="1000"
                    >
                    <figcaption class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/85 via-black/20 to-transparent p-6">
                        <span class="font-bebas text-xl tracking-[0.18em] text-gold-light">{{ $post['handle'] }}</span>
                        <span class="mt-1 max-w-[26ch] text-sm leading-6 text-white/80">{{ $post['caption'] }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
