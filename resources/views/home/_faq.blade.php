@php $sec = $homeSections['faq'] ?? null; @endphp
{{--
    ============================================================
    s15 · FAQ — Alpine accordion + FAQPage JSON-LD (SEO)
    ============================================================
--}}
@php
    // ADMIN-DRIVEN: faqs table
    $faqs = $homepage['faqs']->map(fn ($f) => ['q' => $f->question, 'a' => $f->answer])->all();
    if (empty($faqs)) {
        $faqs = [['q' => 'How long does delivery take across India?', 'a' => 'Metro cities receive orders in 2–4 working days.']];
    }
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
        ], $faqs),
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

<section id="faq" class="relative overflow-hidden bg-rythme-cream py-24 sm:py-32">
    @include('components.instrument-decor')
    <div class="relative z-[1] mx-auto max-w-3xl px-5 sm:px-8">
        <div class="mb-12 text-center" data-reveal="up">
            <p class="section-kicker justify-center">{{ $sec->kicker ?? 'Good to know' }}</p>
            <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Frequently asked <em>questions.</em>@endif</h2>
        </div>

        <div class="space-y-4" x-data="{ open: 0 }">
            @foreach($faqs as $index => $faq)
                <div class="overflow-hidden rounded-2xl border border-rythme-black/10 bg-rythme-warm-white transition-colors duration-300" :class="open === {{ $index }} ? 'border-gold/50 shadow-[0_16px_40px_rgba(10,10,10,0.08)]' : ''" data-reveal="up">
                    <h3 class="m-0">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                            @click="open = open === {{ $index }} ? null : {{ $index }}"
                            :aria-expanded="open === {{ $index }} ? 'true' : 'false'"
                            aria-controls="faq-panel-{{ $index }}"
                        >
                            <span class="font-playfair text-base font-bold text-rythme-black sm:text-lg">{{ $faq['q'] }}</span>
                            <svg class="h-5 w-5 shrink-0 text-gold-dark transition-transform duration-300" :class="open === {{ $index }} ? 'rotate-45' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </button>
                    </h3>
                    <div id="faq-panel-{{ $index }}" x-show="open === {{ $index }}" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                        <p class="px-6 pb-6 text-sm leading-7 text-rythme-black/60 sm:text-base">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-10 text-center text-sm text-rythme-black/50" data-reveal="up">
            Still curious? <a href="/contact" class="text-link font-bold">Talk to a real musician <span aria-hidden="true">↗</span></a>
        </p>
    </div>
</section>
