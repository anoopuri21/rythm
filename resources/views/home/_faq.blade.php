{{--
    ============================================================
    s15 · FAQ — Alpine accordion + FAQPage JSON-LD (SEO)
    ============================================================
--}}
@php
    $faqs = [
        ['q' => 'How long does delivery take across India?', 'a' => 'Metro cities receive orders in 2–4 working days; most other locations in 4–7 working days. Every order ships with tracking and signature-on-delivery, and shipping is free above ₹999.'],
        ['q' => 'Can I return an instrument if I change my mind?', 'a' => 'Yes — you have 7 days from delivery for easy, no-questions returns on unused products in original packaging. Instruments that are played and then returned are covered by our separate 7-day play-trial policy for select guitars and keyboards.'],
        ['q' => 'What warranty do the instruments carry?', 'a' => 'All products include the manufacturer warranty, and select premium instruments carry up to 3 years of extended cover through Rhythm Exports. Warranty support is handled directly by our service partners with doorstep pickup where available.'],
        ['q' => 'Do you offer EMI or easy payment options?', 'a' => 'Yes — no-cost EMI is available on leading credit cards, and we support UPI, net banking, wallets and cash on delivery for eligible orders. The full list of options appears at checkout.'],
        ['q' => 'Are instruments set up before shipping?', 'a' => 'Every guitar, bass, ukulele and keyboard is inspected, tuned and set up by our in-house technicians before dispatch — free of charge. This includes action, intonation and string checks.'],
        ['q' => 'How do I get expert buying advice?', 'a' => 'Call or WhatsApp our team of working musicians for honest, product-specific advice — no scripts. We will help you compare models, plan upgrades and even recommend the right cable.'],
    ];
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
            <p class="section-kicker justify-center">Good to know</p>
            <h2 class="section-title">Frequently asked <em>questions.</em></h2>
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
