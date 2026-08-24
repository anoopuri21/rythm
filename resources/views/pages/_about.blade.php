@php $content = $page->content; @endphp

<div class="bg-paper">
    {{-- Hero — DB content --}}
    <header class="relative overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand/20 blur-[120px]" aria-hidden="true"></div>
        <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28 lg:px-12">
            <p class="section-kicker mb-4 justify-start text-brand-light">Our story</p>
            <h1 class="section-title text-white">{{ $page->title }}</h1>
            @if($content)
                <div class="mt-6 max-w-2xl space-y-4 text-base leading-7 text-white/70 sm:text-lg [&_a]:text-brand-light [&_a]:underline">
                    {!! $content !!}
                </div>
            @endif
        </div>
    </header>

    {{-- Numbers (static) --}}
    <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
            @foreach([
                ['12+', 'Years of craft'],
                ['35,000+', 'Musicians served'],
                ['80+', 'Brands stocked'],
                ['4.8★', 'Average rating'],
            ] as [$value, $label])
                <div class="rounded-3xl border border-ink/10 bg-white p-6 text-center sm:p-8">
                    <p class="font-playfair text-3xl font-bold text-brand sm:text-4xl">{{ $value }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-muted">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Promise --}}
    <section class="mx-auto max-w-7xl px-5 pb-16 sm:px-8 lg:px-12">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="section-kicker mb-3">Our promise</p>
                <h2 class="font-playfair text-3xl font-bold text-ink sm:text-4xl">From Delhi workshops to your doorstep</h2>
                <p class="mt-5 leading-7 text-muted">
                    Our team of working musicians tests every product line before it reaches the store.
                    We stock what we would play ourselves, and we stand behind every single order with
                    honest advice and quick support.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach([
                        'Free expert setup on every guitar',
                        '1-year warranty on all instruments',
                        'Free shipping across India',
                        'Real humans answering your questions',
                    ] as $point)
                        <li class="flex items-start gap-3 text-sm text-ink">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('shop.index') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3.5 text-sm font-bold text-white transition hover:bg-brand-dark">
                    Explore the collection <span aria-hidden="true">→</span>
                </a>
            </div>
            <div class="overflow-hidden rounded-3xl bg-ink p-10 sm:p-14">
                <p class="text-7xl" aria-hidden="true">🎹</p>
                <p class="mt-6 max-w-xs text-sm leading-6 text-white/60">
                    "The right instrument doesn't make you better overnight — it makes
                    every hour of practice worth it." — The Rythme team
                </p>
            </div>
        </div>
    </section>
</div>
