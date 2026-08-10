@php
    $stories = [
        ['category'=>'Studio guide','title'=>'Build a home studio that inspires your best work','excerpt'=>'A practical room-by-room guide to choosing monitors, interfaces and acoustic treatment without overcomplicating it.','image'=>'images/story-studio.jpg','date'=>'August 02, 2026','read'=>'8 min read'],
        ['category'=>'Care & craft','title'=>'How to make your guitar feel new again','excerpt'=>'Simple maintenance rituals every player should know—from fresh strings and fret care to perfect humidity.','image'=>'images/story-guitar.jpg','date'=>'July 26, 2026','read'=>'6 min read'],
        ['category'=>'Buying guide','title'=>'Your first digital piano: what truly matters','excerpt'=>'Key action, sound engines, pedals and connectivity explained in plain language for confident first-time buyers.','image'=>'images/hero-piano.jpg','date'=>'July 18, 2026','read'=>'7 min read'],
    ];
@endphp

<section id="stories" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    @include('components.instrument-decor')
    <div class="relative z-[1] mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="section-kicker">The Rhythm Exports journal</p><h2 class="section-title">Ideas for a life<br><em>lived in music.</em></h2></div><a href="/stories" class="text-link">Read all stories <span>↗</span></a></div>
        <div class="grid gap-8 md:grid-cols-3">
            @foreach($stories as $story)
                <article class="reveal-section story-card group">
                    <a href="/stories/{{ Str::slug($story['title']) }}" class="block overflow-hidden rounded-3xl"><img src="{{ asset($story['image']) }}" alt="" width="1376" height="768" class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" decoding="async"></a>
                    <div class="pt-6"><div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.16em] text-rythme-warm-gray"><span class="text-gold-dark">{{ $story['category'] }}</span><span>{{ $story['read'] }}</span></div><h3 class="mt-4 font-playfair text-2xl leading-snug"><a href="/stories/{{ Str::slug($story['title']) }}" class="transition hover:text-gold-dark">{{ $story['title'] }}</a></h3><p class="mt-3 text-sm leading-6 text-rythme-warm-gray">{{ $story['excerpt'] }}</p><div class="mt-5 flex items-center justify-between border-t border-black/10 pt-4"><time class="text-xs text-rythme-warm-gray">{{ $story['date'] }}</time><a href="/stories/{{ Str::slug($story['title']) }}" class="text-sm font-bold transition group-hover:translate-x-1 group-hover:text-gold-dark">Read story →</a></div></div>
                </article>
            @endforeach
        </div>
    </div>
</section>
