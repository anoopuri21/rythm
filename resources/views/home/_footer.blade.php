@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
    $footerGroups = [
        'Shop' => [
            ['Guitars', '/category/guitars'], ['Keyboards & Pianos', '/category/keyboards-pianos'],
            ['Drums & Percussion', '/category/drums-percussion'], ['Studio & Pro Audio', '/category/pro-audio'],
            ['Indian Instruments', '/category/indian-instruments'], ['Deals', '/deals'],
        ],
        'Customer care' => [
            ['Contact us', '/contact'], ['Shipping & delivery', '/shipping'], ['Returns & refunds', '/returns'],
            ['Warranty', '/warranty'], ['Track your order', '/orders/track'], ['FAQs', '/faqs'],
        ],
        'About Rhythm Exports' => [
            ['Our story', '/about'], ['The Rhythm Exports standard', '/about#standard'], ['Journal', '/stories'],
            ['Careers', '/careers'], ['Privacy', '/privacy'], ['Terms', '/terms'],
        ],
    ];
@endphp

<footer id="footer" class="relative overflow-hidden bg-rythme-black text-white">
    {{-- ============ Fancy CTA section (replaces newsletter) ============ --}}
    <section class="cta-band relative overflow-hidden border-b border-white/10" aria-labelledby="cta-title">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_20%,rgba(212,168,67,.22),transparent_42%),radial-gradient(circle_at_88%_80%,rgba(212,168,67,.14),transparent_40%)]"></div>
        <span class="music-note left-[8%] top-8">♪</span><span class="music-note right-[10%] bottom-6">♫</span>
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[1.25fr_1fr] lg:py-20">
            <div class="reveal-section" data-reveal="up">
                <p class="section-kicker text-gold-light">The Rhythm Exports promise</p>
                <h2 id="cta-title" class="font-playfair text-4xl leading-tight sm:text-5xl">Ready to find <em class="text-gold-light">your sound?</em></h2>
                <p class="mt-4 max-w-lg text-sm leading-7 text-white/55 sm:text-base">
                    Talk to a real musician — not a script. Call, WhatsApp or visit us for honest advice, free setups and instruments that are actually worth it.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="/contact" class="btn-gold btn-shine">Talk to an expert <span aria-hidden="true">→</span></a>
                    <a href="https://wa.me/919000000000" target="_blank" rel="noopener noreferrer" class="btn-ghost-light">WhatsApp us <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <div class="reveal-section grid grid-cols-2 gap-4 sm:grid-cols-2" data-reveal="up">
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="font-bebas text-3xl text-gold-light">24×7</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Expert support for every question, big or small</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="font-bebas text-3xl text-gold-light">15+ yrs</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Serving musicians across India since 2009</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="font-bebas text-3xl text-gold-light">40+</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">World-class brands, handpicked &amp; guaranteed</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="font-bebas text-3xl text-gold-light">₹0</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Free setup on every instrument, always</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Footer body ============ --}}
    <div class="mx-auto max-w-7xl px-5 pb-8 pt-16 sm:px-8 lg:pt-20">
        <div class="grid gap-12 border-b border-white/10 pb-14 sm:grid-cols-2 lg:grid-cols-[1.25fr_repeat(3,1fr)] lg:gap-10">
            <div class="reveal-section pr-0 lg:pr-12">
                <a href="{{ route('home') }}" class="inline-flex flex-col" aria-label="{{ $brand }} home">
                    <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-10 w-auto brightness-0 invert" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                    <span class="mt-2 hidden text-[9px] tracking-[0.35em] text-gold" style="display:none" aria-hidden="true">RHYTHM EXPORTS</span>
                </a>
                <p class="mt-6 max-w-xs text-sm leading-7 text-white/45">Premium instruments, thoughtful advice, and a lifelong belief that everyone deserves to find their sound.</p>
                <div class="mt-7 flex gap-3" aria-label="Social media links">
                    <a href="https://instagram.com" rel="noopener noreferrer" target="_blank" class="social-link" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke-width="1.6"/><circle cx="12" cy="12" r="4" stroke-width="1.6"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="https://youtube.com" rel="noopener noreferrer" target="_blank" class="social-link" aria-label="YouTube">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 12s0-4-1-5-2-1-8-1-7 0-8 1-1 5-1 5 0 4 1 5 2 1 8 1 7 0 8-1 1-5 1-5z" stroke-width="1.6"/><path d="m10 9 5 3-5 3V9z" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="https://facebook.com" rel="noopener noreferrer" target="_blank" class="social-link" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v2H6v4h3v7h4v-7h3l1-4h-4V9c0-.7.3-1 1-1z"/></svg>
                    </a>
                    <a href="https://x.com" rel="noopener noreferrer" target="_blank" class="social-link" aria-label="X">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.5 3H22l-7.6 8.7L23 21h-6.7l-5.2-6.8L5.2 21H1.7l7.8-8.9L1.2 3H8l4.7 6.2L18.5 3zm-1.2 16h1.9L7 4.9H5L17.3 19z"/></svg>
                    </a>
                </div>
            </div>

            @foreach($footerGroups as $heading => $links)
                <nav class="reveal-section" aria-labelledby="footer-{{ Str::slug($heading) }}">
                    <h3 id="footer-{{ Str::slug($heading) }}" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">{{ $heading }}</h3>
                    <ul class="mt-6 space-y-3.5">
                        @foreach($links as [$label, $url])
                            <li><a href="{{ $url }}" class="footer-link text-sm text-white/45">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>

        <div class="flex flex-col gap-5 py-7 text-[11px] text-white/35 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="inline-flex items-center gap-2"><span class="text-gold">●</span> Secure payments</span>
                <span>Visa</span><span>Mastercard</span><span>UPI</span><span>Razorpay</span>
            </div>
        </div>
    </div>

    <p class="pointer-events-none -mb-[0.16em] select-none text-center font-bebas text-[20vw] leading-[0.72] tracking-[0.02em] text-white/[0.025]" aria-hidden="true">RHYTHM</p>
</footer>
