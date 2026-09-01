@php
    /** @var \App\Models\Page $page */
    $content = $page->content;
    $s = $page->settings ?? [];

    $heroKicker = $s['hero_kicker'] ?? 'Our story';

    $stats = collect($s['stats'] ?? [])
        ->filter(fn ($row) => filled($row['value'] ?? null) && filled($row['label'] ?? null))
        ->values();
    if ($stats->isEmpty()) {
        $stats = collect([
            ['value' => 'Curated', 'label' => 'Instrument catalogue'],
            ['value' => 'Verified', 'label' => 'Checkout totals'],
            ['value' => 'Moderated', 'label' => 'Reviews & Q&A'],
            ['value' => 'Protected', 'label' => 'Order tracking'],
        ]);
    }

    $promiseKicker = $s['promise_kicker'] ?? 'Our promise';
    $promiseHeading = $s['promise_heading'] ?? 'A clearer way to explore musical instruments';
    $promiseText = $s['promise_text'] ?? 'Rhythm Exports presents catalogue details, availability and checkout totals through the storefront. Product questions and verified-purchase reviews are moderated before they appear publicly.';

    $points = collect($s['promise_points'] ?? [])->filter(fn ($p) => filled($p))->values();
    if ($points->isEmpty()) {
        $points = collect([
            'Category, brand, price and specification filters',
            'Server-verified checkout totals',
            'Moderated product questions and staff answers',
            'Protected order tracking and invoice access',
        ]);
    }

    $ctaLabel = $s['cta_label'] ?? 'Explore the collection';
    $ctaUrl = filled($s['cta_url'] ?? null) ? $s['cta_url'] : route('shop.index');
    $quoteEmoji = $s['quote_emoji'] ?? '🎹';
    $quoteText = $s['quote_text'] ?? '"The right instrument doesn\'t make you better overnight — it makes every hour of practice worth it." — The Rythme team';

    $valuesHeading = $s['values_heading'] ?? 'What we stand for';
    $values = collect($s['values'] ?? [])
        ->filter(fn ($row) => filled($row['title'] ?? null))
        ->values();
@endphp

<div class="bg-paper">
    {{-- Hero — DB content --}}
    <header class="relative overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand/20 blur-[120px]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-brand/10 blur-[110px]" aria-hidden="true"></div>
        <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-28 lg:px-12">
            <p class="section-kicker mb-4 justify-start text-brand-light">{{ $heroKicker }}</p>
            <h1 class="section-title text-white">{{ $page->title }}</h1>
            @if($content)
                <div class="mt-6 max-w-2xl space-y-4 text-base leading-7 text-white/70 sm:text-lg [&_a]:text-brand-light [&_a]:underline">
                    {!! $content !!}
                </div>
            @endif
        </div>
    </header>

@php
    $statCols = ['sm:grid-cols-1', 'sm:grid-cols-2', 'sm:grid-cols-3', 'sm:grid-cols-4'][min($stats->count(), 4) - 1];
@endphp
    {{-- Highlight stats — admin managed --}}
    <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
        <div class="grid grid-cols-2 gap-6 {{ $statCols }}">
            @foreach($stats as $stat)
                <div class="rounded-3xl border border-ink/10 bg-white p-6 text-center sm:p-8">
                    <p class="font-playfair text-3xl font-bold text-brand sm:text-4xl">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-muted">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Promise — admin managed --}}
    <section class="mx-auto max-w-7xl px-5 pb-16 sm:px-8 lg:px-12">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="section-kicker mb-3">{{ $promiseKicker }}</p>
                <h2 class="font-playfair text-3xl font-bold text-ink sm:text-4xl">{{ $promiseHeading }}</h2>
                @if($promiseText)
                    <p class="mt-5 leading-7 text-muted">{{ $promiseText }}</p>
                @endif
                <ul class="mt-6 space-y-3">
                    @foreach($points as $point)
                        <li class="flex items-start gap-3 text-sm text-ink">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
                @if($ctaLabel)
                    <a href="{{ $ctaUrl }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3.5 text-sm font-bold text-white transition hover:bg-brand-dark">
                        {{ $ctaLabel }} <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
            <div class="overflow-hidden rounded-3xl bg-ink p-10 sm:p-14">
                <p class="text-7xl" aria-hidden="true">{{ $quoteEmoji }}</p>
                <p class="mt-6 max-w-xs text-sm leading-6 text-white/60">{{ $quoteText }}</p>
            </div>
        </div>
    </section>

    {{-- Values — admin managed, hidden when empty --}}
    @if($values->isNotEmpty())
        <section class="border-t border-ink/10 bg-white">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
                <p class="section-kicker mb-3">Values</p>
                <h2 class="font-playfair text-3xl font-bold text-ink sm:text-4xl">{{ $valuesHeading }}</h2>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($values as $value)
                        <div class="rounded-3xl border border-ink/10 bg-paper p-7">
                            @if(filled($value['icon'] ?? null))
                                <span class="text-3xl" aria-hidden="true">{{ $value['icon'] }}</span>
                            @endif
                            <h3 class="mt-4 text-base font-bold text-ink">{{ $value['title'] }}</h3>
                            @if(filled($value['text'] ?? null))
                                <p class="mt-2 text-sm leading-6 text-muted">{{ $value['text'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
