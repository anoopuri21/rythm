@php $sec = $homeSections['testimonials'] ?? null; @endphp
@php
    $testimonials = [
        [
            'quote' => 'The guitar arrived perfectly set up—low action, spot-on intonation, and packed like it was touring the country. It felt personal, not transactional.',
            'name' => 'Aarav Mehta',
            'role' => 'Guitarist · Mumbai',
            'initials' => 'AM',
            'purchase' => 'Fender Player II Stratocaster',
        ],
        [
            'quote' => 'I was building my first home studio and the advice was refreshingly honest. Rhythm Exports helped me spend where it mattered and save where it did not.',
            'name' => 'Naina Kapoor',
            'role' => 'Singer-songwriter · Delhi',
            'initials' => 'NK',
            'purchase' => 'Studio recording bundle',
        ],
        [
            'quote' => 'From the first call to delivery, everyone spoke the language of musicians. My keyboard reached Bengaluru ahead of schedule and was ready for rehearsal.',
            'name' => 'Rohan Iyer',
            'role' => 'Keys player · Bengaluru',
            'initials' => 'RI',
            'purchase' => 'Yamaha CK61 Stage Keyboard',
        ],
        [
            'quote' => 'Finding an authentic tabla set online felt risky. The detailed consultation, careful tuning, and beautiful instrument changed my mind completely.',
            'name' => 'Ishita Sen',
            'role' => 'Classical musician · Kolkata',
            'initials' => 'IS',
            'purchase' => 'Professional brass tabla set',
        ],
    ];
@endphp

<section id="testimonials" class="testimonial-section relative overflow-hidden bg-rythme-black-soft py-24 text-white sm:py-32">
    <div class="pointer-events-none absolute left-1/2 top-0 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-gold/5 blur-[120px]"></div>
    <div class="absolute left-6 top-16 select-none font-playfair text-[14rem] leading-none text-white/[0.025] sm:left-16 sm:text-[22rem]" aria-hidden="true">“</div>

    <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mx-auto mb-14 max-w-3xl text-center">
            <p class="section-kicker justify-center text-gold-light">{{ $sec->kicker ?? 'Stories from the Rhythm Exports community' }}</p>
            <h2 class="section-title mx-auto text-white">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Made for musicians.<br><em class="text-gold-light">Loved by musicians.</em>@endif</h2>
            <div class="mt-6 flex items-center justify-center gap-3 text-sm text-white/55">
                <span class="tracking-[0.2em] text-gold" aria-label="5 out of 5 stars">★★★★★</span>
                <span><strong class="text-white">4.9</strong> from 2,400+ verified reviews</span>
            </div>
        </div>

        <div class="testimonial-swiper swiper !overflow-visible">
            <div class="swiper-wrapper items-stretch">
                @foreach($testimonials as $testimonial)
                    <div class="swiper-slide h-auto">
                        <figure class="testimonial-card flex h-full flex-col rounded-[2rem] border border-white/10 bg-white/[0.045] p-7 backdrop-blur-sm sm:p-9">
                            <div class="mb-7 flex items-start justify-between gap-4">
                                <span class="font-playfair text-6xl leading-none text-gold" aria-hidden="true">“</span>
                                <span class="rounded-full border border-gold/25 bg-gold/10 px-3 py-1 text-[9px] font-bold uppercase tracking-[0.18em] text-gold-light">Verified buyer</span>
                            </div>
                            <blockquote class="flex-1 font-playfair text-2xl leading-relaxed text-white/90">{{ $testimonial['quote'] }}</blockquote>
                            <figcaption class="mt-9 border-t border-white/10 pt-6">
                                <div class="flex items-center gap-4">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-gold-light to-gold-dark text-xs font-bold text-rythme-black">{{ $testimonial['initials'] }}</span>
                                    <span class="min-w-0 flex-1">
                                        <strong class="block text-sm text-white">{{ $testimonial['name'] }}</strong>
                                        <span class="mt-1 block text-xs text-white/45">{{ $testimonial['role'] }}</span>
                                    </span>
                                </div>
                                <p class="mt-4 truncate text-[10px] uppercase tracking-[0.15em] text-gold/70">Purchased · {{ $testimonial['purchase'] }}</p>
                            </figcaption>
                        </figure>
                    </div>
                @endforeach
            </div>
            <div class="testimonial-pagination swiper-pagination !relative !bottom-auto mt-10"></div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-3">
            <button type="button" class="testimonial-prev flex h-12 w-12 items-center justify-center rounded-full border border-white/15 text-white transition hover:border-gold hover:text-gold" aria-label="Previous testimonial">←</button>
            <button type="button" class="testimonial-next flex h-12 w-12 items-center justify-center rounded-full border border-white/15 text-white transition hover:border-gold hover:text-gold" aria-label="Next testimonial">→</button>
        </div>
    </div>
</section>
