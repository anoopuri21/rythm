@php
    $footerGroups = [
        'Shop' => [
            ['Guitars', '/category/guitars'], ['Keys & Pianos', '/category/keyboards-pianos'],
            ['Drums & Percussion', '/category/drums-percussion'], ['Studio & Pro Audio', '/category/pro-audio'],
            ['Indian Instruments', '/category/indian-instruments'], ['Deals', '/deals'],
        ],
        'Customer care' => [
            ['Contact us', '/contact'], ['Shipping & delivery', '/shipping'], ['Returns & refunds', '/returns'],
            ['Warranty', '/warranty'], ['Track your order', '/orders/track'], ['FAQs', '/faqs'],
        ],
        'About Rythme' => [
            ['Our story', '/about'], ['The Rythme standard', '/about#standard'], ['Journal', '/stories'],
            ['Careers', '/careers'], ['Privacy', '/privacy'], ['Terms', '/terms'],
        ],
    ];
@endphp

<footer id="footer" class="relative overflow-hidden bg-rythme-black text-white">
    <section class="newsletter-strip relative border-b border-white/10" aria-labelledby="newsletter-title">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_50%,rgba(212,168,67,.14),transparent_35%)]"></div>
        <div class="relative mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 lg:grid-cols-[1fr_1.15fr] lg:items-center lg:py-16">
            <div class="reveal-section">
                <p class="section-kicker text-gold-light">Stay in the loop</p>
                <h2 id="newsletter-title" class="font-playfair text-3xl sm:text-4xl">Fresh gear. Better stories.<br><em class="text-gold-light">No noise.</em></h2>
            </div>
            <form class="newsletter-form reveal-section" action="{{ route('newsletter.store') }}" method="POST" novalidate>
                @csrf
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="newsletter-company">Company</label>
                    <input id="newsletter-company" name="company" type="text" tabindex="-1" autocomplete="off">
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <label class="sr-only" for="newsletter-email">Email address</label>
                        <input id="newsletter-email" name="email" type="email" inputmode="email" autocomplete="email" required maxlength="254" placeholder="Your email address" class="h-14 w-full rounded-full border border-white/15 bg-white/[0.06] px-6 pr-12 text-sm text-white placeholder:text-white/35 focus:border-gold focus:ring-gold">
                        <svg class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <button type="submit" class="newsletter-submit h-14 shrink-0 rounded-full bg-gold px-7 text-sm font-bold text-rythme-black transition hover:bg-gold-light disabled:cursor-wait disabled:opacity-70">
                        <span>Join the list</span>
                    </button>
                </div>
                <p class="newsletter-feedback mt-3 min-h-5 text-xs {{ $errors->has('email') ? 'text-red-300' : (session('newsletter_status') ? 'text-gold-light' : 'text-white/45') }}" role="status" aria-live="polite">
                    {{ $errors->first('email') ?: (session('newsletter_status') ?: 'Monthly inspiration and members-only offers. Unsubscribe anytime.') }}
                </p>
            </form>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-5 pb-8 pt-16 sm:px-8 lg:pt-20">
        <div class="grid gap-12 border-b border-white/10 pb-14 sm:grid-cols-2 lg:grid-cols-[1.25fr_repeat(3,1fr)] lg:gap-10">
            <div class="reveal-section pr-0 lg:pr-12">
                <a href="{{ route('home') }}" class="inline-flex flex-col" aria-label="Rythme Music Store home">
                    <span class="font-playfair text-3xl font-bold tracking-[0.12em]">RYTHME</span>
                    <span class="mt-1 text-[9px] tracking-[0.35em] text-gold">MUSIC STORE</span>
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
            <p>© {{ date('Y') }} Rythme Music Store. All rights reserved.</p>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="inline-flex items-center gap-2"><span class="text-gold">●</span> Secure payments</span>
                <span>Visa</span><span>Mastercard</span><span>UPI</span><span>Razorpay</span>
            </div>
        </div>
    </div>

    <p class="pointer-events-none -mb-[0.16em] select-none text-center font-bebas text-[20vw] leading-[0.72] tracking-[0.02em] text-white/[0.025]" aria-hidden="true">RYTHME</p>
</footer>
