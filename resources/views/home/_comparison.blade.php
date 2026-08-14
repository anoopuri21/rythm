@php $sec = $homeSections['comparison'] ?? null; @endphp
{{--
    ============================================================
    s13 · Comparison — "Rhythm Exports vs. the rest."
    Light section, gold check / muted cross rows, responsive.
    ============================================================
--}}
@php
    $comparisons = [
        ['feature' => 'Premium brands', 'us' => '40+ world-class brands, handpicked', 'them' => 'Limited, generic selection'],
        ['feature' => 'Price promise', 'us' => 'Best price, matched with confidence', 'them' => 'Varies store to store'],
        ['feature' => 'Free shipping', 'us' => 'Free across India over ₹999', 'them' => 'High thresholds + hidden fees'],
        ['feature' => 'EMI options', 'us' => 'No-cost EMI on leading cards', 'them' => 'Often unavailable'],
        ['feature' => 'Return policy', 'us' => '7-day easy, no-questions returns', 'them' => 'Usually 3 days, with fine print'],
        ['feature' => 'Warranty support', 'us' => 'Up to 3-year cover on select gear', 'them' => 'Standard 1-year only'],
        ['feature' => 'Expert advice', 'us' => 'Real musicians, not scripts', 'them' => 'Call centres reading FAQs'],
        ['feature' => 'Setup & service', 'us' => 'Free first setup on every instrument', 'them' => 'Extra charges everywhere'],
    ];
@endphp

<section id="comparison" class="relative overflow-hidden bg-rythme-cream-dark py-24 sm:py-32">
    <div class="pointer-events-none absolute -right-24 top-0 select-none font-playfair text-[16rem] leading-none text-rythme-black/[0.03]" aria-hidden="true">vs</div>
    <div class="relative mx-auto max-w-6xl px-5 sm:px-8">
        <div class="mx-auto mb-14 max-w-2xl text-center" data-reveal="up">
            <p class="section-kicker justify-center">{{ $sec->kicker ?? 'The Rhythm Exports difference' }}</p>
            <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Rhythm Exports vs. <em>the rest.</em>@endif</h2>
            <p class="mt-5 text-base leading-7 text-rythme-black/60">
                We built Rhythm Exports around the questions musicians actually ask before buying. Here is how we answer them — compared, honestly.
            </p>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-rythme-black/10 bg-rythme-warm-white shadow-[0_30px_80px_rgba(10,10,10,0.08)]" data-reveal="up">
            <table class="w-full min-w-[640px] border-collapse text-left">
                <thead>
                    <tr class="border-b border-rythme-black/10">
                        <th scope="col" class="px-6 py-5 text-xs font-bold uppercase tracking-[0.2em] text-rythme-black/50">What matters</th>
                        <th scope="col" class="px-6 py-5 text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">
                            <span class="flex items-center gap-2">Rhythm Exports <span class="h-px w-6 bg-gold"></span></span>
                        </th>
                        <th scope="col" class="px-6 py-5 text-xs font-bold uppercase tracking-[0.2em] text-rythme-black/40">Typical stores</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rythme-black/[0.06]">
                    @foreach($comparisons as $row)
                        <tr class="transition-colors duration-300 hover:bg-gold/[0.06]">
                            <th scope="row" class="px-6 py-4 font-playfair text-base font-bold text-rythme-black sm:text-lg">{{ $row['feature'] }}</th>
                            <td class="px-6 py-4">
                                <span class="flex items-start gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm font-medium leading-6 text-rythme-black">{{ $row['us'] }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-start gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-rythme-black/25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span class="text-sm leading-6 text-rythme-black/45">{{ $row['them'] }}</span>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-5 text-center text-xs text-rythme-black/40" data-reveal="up">*Compared against policies commonly published by leading Indian music retailers. Terms may vary by product.</p>
    </div>
</section>
