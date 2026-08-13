@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
    $cats = app(\App\Services\CategoryService::class)->tree();
    $brands = app(\App\Services\BrandService::class)->allWithCounts();
@endphp

<footer id="footer" class="relative overflow-hidden bg-rythme-black text-white">
    {{-- ============ CTA band ============ --}}
    <section class="cta-band relative overflow-hidden border-b border-white/10" aria-labelledby="cta-title">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_20%,rgba(213,8,8,.22),transparent_42%),radial-gradient(circle_at_88%_80%,rgba(213,8,8,.14),transparent_40%)]"></div>
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
            <div class="reveal-section grid grid-cols-2 gap-4" data-reveal="up">
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

    {{-- ============ Brand block ============ --}}
    <div class="border-b border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-6 px-5 py-12 text-center sm:px-8 lg:flex-row lg:justify-between lg:text-left">
            <div>
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center lg:items-start" aria-label="{{ $brand }} home">
                    <img src="{{ $logo }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-10 w-auto brightness-0 invert" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                    <span class="mt-2 hidden text-[9px] tracking-[0.35em] text-gold" style="display:none" aria-hidden="true">RHYTHM EXPORTS</span>
                </a>
                <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-white/45 lg:mx-0">Premium instruments, thoughtful advice, and a lifelong belief that everyone deserves to find their sound.</p>
            </div>
            <div class="flex gap-3" aria-label="Social media links">
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
    </div>

    {{-- ============ 5-column link grid ============ --}}
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8">
        <div class="grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-5">
            {{-- Shop — DB categories --}}
            <nav aria-labelledby="footer-shop">
                <h3 id="footer-shop" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Shop</h3>
                <ul class="mt-6 space-y-3.5">
                    @foreach(array_slice($cats, 0, 5) as $cat)
                        <li><a href="/shop?category={{ $cat['slug'] }}" class="footer-link text-sm text-white/45">{{ $cat['name'] }}</a></li>
                    @endforeach
                    <li><a href="/shop" class="footer-link text-sm font-semibold text-white/60">All products <span aria-hidden="true">→</span></a></li>
                </ul>
            </nav>

            {{-- Top brands — DB brands --}}
            <nav aria-labelledby="footer-brands">
                <h3 id="footer-brands" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Top brands</h3>
                <ul class="mt-6 space-y-3.5">
                    @foreach($brands->take(5) as $brandItem)
                        <li><a href="/shop?brand[]={{ $brandItem->slug }}" class="footer-link text-sm text-white/45">{{ $brandItem->name }}</a></li>
                    @endforeach
                </ul>
            </nav>

            {{-- Customer care — dynamic pages --}}
            <nav aria-labelledby="footer-care">
                <h3 id="footer-care" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Customer care</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/contact" class="footer-link text-sm text-white/45">Contact us</a></li>
                    <li><a href="/shipping" class="footer-link text-sm text-white/45">Shipping &amp; delivery</a></li>
                    <li><a href="/returns" class="footer-link text-sm text-white/45">Returns &amp; refunds</a></li>
                    <li><a href="/warranty" class="footer-link text-sm text-white/45">Warranty</a></li>
                    <li><a href="/faqs" class="footer-link text-sm text-white/45">FAQs</a></li>
                </ul>
            </nav>

            {{-- Company --}}
            <nav aria-labelledby="footer-company">
                <h3 id="footer-company" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Company</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/about" class="footer-link text-sm text-white/45">Our story</a></li>
                    <li><a href="/terms" class="footer-link text-sm text-white/45">Terms &amp; conditions</a></li>
                    <li><a href="/privacy" class="footer-link text-sm text-white/45">Privacy policy</a></li>
                    <li><a href="/contact" class="footer-link text-sm text-white/45">Careers</a></li>
                </ul>
            </nav>

            {{-- Help --}}
            <nav aria-labelledby="footer-help">
                <h3 id="footer-help" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Help</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/track-order" class="footer-link text-sm text-white/45">Track your order</a></li>
                    <li><a href="/faqs" class="footer-link text-sm text-white/45">Help centre</a></li>
                    <li><a href="/cart" class="footer-link text-sm text-white/45">View cart</a></li>
                    <li><a href="/wishlist" class="footer-link text-sm text-white/45">Wishlist</a></li>
                    <li><a href="/login" class="footer-link text-sm text-white/45">My account</a></li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- ============ Bottom bar + trust badges ============ --}}
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-7 text-[11px] text-white/35 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="inline-flex items-center gap-2"><span class="text-gold">●</span> Secure payments</span>
                <span>Visa</span><span>Mastercard</span><span>UPI</span><span>Razorpay</span>
            </div>
        </div>
    </div>

    <p class="pointer-events-none -mb-[0.16em] select-none text-center font-bebas text-[20vw] leading-[0.72] tracking-[0.02em] text-white/[0.025]" aria-hidden="true">RHYTHM</p>
</footer>
