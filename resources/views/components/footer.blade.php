@php
    $brand = config('rythme.brand_name');
    $logo = config('rythme.logo_url');
    $cats = app(\App\Services\CategoryService::class)->tree();
    $brands = app(\App\Services\BrandService::class)->allWithCounts();
@endphp

<footer id="footer" class="relative overflow-hidden bg-rythme-black text-white">
    {{-- ============ CTA band ============ --}}
    <section class="cta-band relative overflow-hidden border-b border-white/10" aria-labelledby="cta-title">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_20%,rgba(255,255,255,.07),transparent_42%),radial-gradient(circle_at_88%_80%,rgba(255,255,255,.05),transparent_40%)]"></div>
        <span class="music-note left-[8%] top-8">♪</span><span class="music-note right-[10%] bottom-6">♫</span>
        <div class="relative mx-auto grid max-w-[1520px] items-center gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[1.25fr_1fr] lg:py-20">
            <div class="reveal-section" data-reveal="up">
                <p class="section-kicker text-gold-light">The Rhythm Exports promise</p>
                <h2 id="cta-title" class="font-playfair text-4xl leading-tight sm:text-5xl">Ready to find <em class="text-gold-light">your sound?</em></h2>
                <p class="mt-4 max-w-lg text-sm leading-7 text-white/55 sm:text-base">
                    Explore the catalogue or contact the Rhythm Exports team with a product or order question.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="/shop" class="btn-gold btn-shine">Browse instruments <span aria-hidden="true">→</span></a>
                    <a href="/contact" class="btn-ghost-light">Contact us <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="reveal-section grid grid-cols-2 gap-4" data-reveal="up">
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="text-base font-bold text-gold-light">Catalogue filters</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Narrow instruments by category, brand, price, stock and available specifications.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="text-base font-bold text-gold-light">Verified totals</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Checkout totals are recalculated from current catalogue data.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="text-base font-bold text-gold-light">Wishlist</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Save products to a customer account for later consideration.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition hover:border-gold/40">
                    <p class="text-base font-bold text-gold-light">Order tracking</p>
                    <p class="mt-1 text-xs leading-5 text-white/55">Follow recorded order-status updates through protected access.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Brand block ============ --}}
    <div class="border-b border-white/10">
        <div class="mx-auto flex max-w-[1520px] flex-col items-center gap-6 px-5 py-12 text-center sm:px-8 lg:flex-row lg:justify-between lg:text-left">
            <div>
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center lg:items-start" aria-label="{{ $brand }} home">
                    <img src="{{ \Illuminate\Support\Facades\URL::to($logo) }}" alt="{{ $brand }} logo" width="1466" height="434" class="h-10 w-auto brightness-0 invert" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                    <span class="mt-2 hidden text-[9px] tracking-[0.35em] text-gold" style="display:none" aria-hidden="true">RHYTHM EXPORTS</span>
                </a>
                <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-white/45 lg:mx-0">Browse musical instruments, studio gear and accessories through the Rhythm Exports catalogue.</p>
            </div>

        </div>
    </div>

    {{-- ============ 5-column link grid ============ --}}
    <div class="mx-auto max-w-[1520px] px-5 py-14 sm:px-8">
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

            {{-- Top brands — render only when real brand data exists --}}
            @if($brands->isNotEmpty())
                <nav aria-labelledby="footer-brands">
                    <h3 id="footer-brands" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Top brands</h3>
                    <ul class="mt-6 space-y-3.5">
                        @foreach($brands->take(5) as $brandItem)
                            <li><a href="/shop?brand[]={{ $brandItem->slug }}" class="footer-link text-sm text-white/45">{{ $brandItem->name }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            {{-- Customer care — dynamic pages --}}
            <nav aria-labelledby="footer-care">
                <h3 id="footer-care" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Customer care</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/contact" class="footer-link text-sm text-white/45">Contact us</a></li>
                </ul>
            </nav>

            {{-- Company --}}
            <nav aria-labelledby="footer-company">
                <h3 id="footer-company" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Company</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/about" class="footer-link text-sm text-white/45">Our story</a></li>
                    <li><a href="/terms" class="footer-link text-sm text-white/45">Terms &amp; conditions</a></li>
                    <li><a href="/privacy" class="footer-link text-sm text-white/45">Privacy policy</a></li>
                </ul>
            </nav>

            {{-- Help --}}
            <nav aria-labelledby="footer-help">
                <h3 id="footer-help" class="text-xs font-bold uppercase tracking-[0.2em] text-gold-light">Help</h3>
                <ul class="mt-6 space-y-3.5">
                    <li><a href="/track-order" class="footer-link text-sm text-white/45">Track your order</a></li>
                    <li><a href="/cart" class="footer-link text-sm text-white/45">View cart</a></li>
                    <li><a href="/wishlist" class="footer-link text-sm text-white/45">Wishlist</a></li>
                    <li><a href="/login" class="footer-link text-sm text-white/45">My account</a></li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- ============ Bottom bar + trust badges ============ --}}
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-[1520px] flex-col gap-5 px-5 py-7 text-[11px] text-white/35 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
            <p class="inline-flex items-center gap-2"><span class="text-gold">●</span> Server-verified checkout totals</p>
        </div>
    </div>

    <p class="pointer-events-none -mb-[0.16em] select-none text-center font-bebas text-[20vw] leading-[0.72] tracking-[0.02em] text-white/[0.025]" aria-hidden="true">RHYTHM</p>
</footer>
