@php
    /** @var \App\Models\Page $page */
    $content = $page->content;
    $s = $page->settings ?? [];

    $kicker = $s['contact_kicker'] ?? "We're listening";

    $cards = collect($s['cards'] ?? [])
        ->filter(fn ($row) => filled($row['title'] ?? null))
        ->values();
    if ($cards->isEmpty()) {
        $cards = collect([
            ['icon' => '🎧', 'title' => 'Support & orders', 'line1' => 'support@rythme.store', 'line2' => '+91 98765 43210', 'line3' => 'Mon–Sat, 10am–7pm IST'],
            ['icon' => '🏠', 'title' => 'Showroom', 'line1' => '42, Music Lane, Karol Bagh', 'line2' => 'New Delhi, Delhi 110005', 'line3' => 'Walk-ins welcome'],
            ['icon' => '🤝', 'title' => 'Partnerships', 'line1' => 'partners@rythme.store', 'line2' => 'Brands, dealers, teachers', 'line3' => 'Reply within 2 days'],
        ]);
    }

    $whatsappEnabled = (bool) ($s['whatsapp_enabled'] ?? true);
    $whatsappNumber = $s['whatsapp_number'] ?? '+91 98765 43210';
    $whatsappDigits = preg_replace('/\D+/', '', (string) $whatsappNumber);
    $whatsappTitle = $s['whatsapp_title'] ?? 'Prefer WhatsApp?';
    $whatsappText = $s['whatsapp_text'] ?? 'Message us photos of your gear — we love a good setup question.';
    $whatsappButton = $s['whatsapp_button'] ?? 'Chat on WhatsApp';

    // Only trusted Google Maps embed URLs are rendered inside the iframe.
    $mapEmbedUrl = $s['map_embed_url'] ?? null;
    if (! is_string($mapEmbedUrl) || ! str_starts_with($mapEmbedUrl, 'https://www.google.com/maps/embed')) {
        $mapEmbedUrl = null;
    }
@endphp

<div class="bg-paper">
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-12">
        <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
            <span aria-hidden="true" class="text-ink/30">/</span>
            <span class="font-semibold text-ink" aria-current="page">Contact</span>
        </nav>

        <p class="section-kicker mb-4">{{ $kicker }}</p>
        <h1 class="section-title">{{ $page->title }}</h1>
        @if($content)
            <div class="mt-5 max-w-2xl space-y-4 text-base leading-7 text-muted sm:text-lg [&_a]:text-brand [&_a]:underline">
                {!! $content !!}
            </div>
        @endif

        <div class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] lg:gap-14">
            {{-- Form --}}
            <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-9">
                @if(session('contact_success'))
                    <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700" role="status">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        {{ session('contact_success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Your name</span>
                        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="Anoop Puri">
                        @error('name') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="you@example.com">
                        @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Phone (optional)</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="98765 43210">
                        @error('phone') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Subject (optional)</span>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="Order help, setup advice…">
                    </label>
                    <input type="text" name="company" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Message</span>
                        <textarea name="message" rows="6" required
                                  class="w-full rounded-xl border border-ink/15 bg-paper px-4 py-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                                  placeholder="Tell us how we can help…">{{ old('message') }}</textarea>
                        @error('message') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                    </label>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-8 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                            Send message <span aria-hidden="true">→</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info — admin managed --}}
            <aside class="space-y-5">
                @foreach($cards as $card)
                    <div class="flex gap-4 rounded-3xl border border-ink/10 bg-white p-6">
                        @if(filled($card['icon'] ?? null))
                            <span class="text-2xl" aria-hidden="true">{{ $card['icon'] }}</span>
                        @endif
                        <div>
                            <h2 class="text-sm font-bold text-ink">{{ $card['title'] }}</h2>
                            @if(filled($card['line1'] ?? null))
                                <p class="mt-1.5 text-sm font-semibold text-brand">{{ $card['line1'] }}</p>
                            @endif
                            @if(filled($card['line2'] ?? null))
                                <p class="text-sm text-muted">{{ $card['line2'] }}</p>
                            @endif
                            @if(filled($card['line3'] ?? null))
                                <p class="text-xs text-muted">{{ $card['line3'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($whatsappEnabled && $whatsappDigits !== '')
                    <div class="rounded-3xl bg-ink p-6 text-white">
                        <h2 class="text-sm font-bold">{{ $whatsappTitle }}</h2>
                        <p class="mt-1.5 text-sm text-white/60">{{ $whatsappText }}</p>
                        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer"
                           class="mt-4 inline-block rounded-full bg-brand px-6 py-2.5 text-xs font-bold text-white transition hover:bg-brand-dark">
                            {{ $whatsappButton }}
                        </a>
                    </div>
                @endif
            </aside>
        </div>

        {{-- Map — admin managed, hidden when no embed URL is set --}}
        @if($mapEmbedUrl)
            <div class="mt-12 overflow-hidden rounded-3xl border border-ink/10 bg-white">
                <iframe
                    src="{{ $mapEmbedUrl }}"
                    title="Store location map"
                    class="h-80 w-full sm:h-96"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        @endif
    </div>
</div>
