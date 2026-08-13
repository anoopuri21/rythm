@extends('layouts.app')

@section('title', 'Contact Us — Rythme Music Store')
@section('meta_description', 'Questions about an instrument, an order or a setup? Contact the Rythme Music Store team — we reply within 24 hours.')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-12">
            <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
                <span aria-hidden="true" class="text-ink/30">/</span>
                <span class="font-semibold text-ink" aria-current="page">Contact</span>
            </nav>

            <p class="section-kicker mb-4">We're listening</p>
            <h1 class="section-title">Talk to a real musician.</h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-muted sm:text-lg">
                Setup advice, order questions, warranty help — our team plays the same
                instruments we sell. Write to us and you will hear back within 24 hours.
            </p>

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

                        {{-- Honeypot --}}
                        <input type="text" name="company" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Message</span>
                            <textarea name="message" rows="6" required
                                      class="w-full rounded-xl border border-ink/15 bg-paper px-4 py-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                                      placeholder="Tell us how we can help…">{{ old('message') }}</textarea>
                            @error('message') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                        </label>

                        <div class="sm:col-span-2">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-8 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(213,8,8,0.25)] transition hover:bg-brand-dark">
                                Send message <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Info --}}
                <aside class="space-y-5">
                    @foreach([
                        ['🎧', 'Support & orders', 'support@rythme.store', '+91 98765 43210', 'Mon–Sat, 10am–7pm IST'],
                        ['🏠', 'Showroom', '42, Music Lane, Karol Bagh', 'New Delhi, Delhi 110005', 'Walk-ins welcome'],
                        ['🤝', 'Partnerships', 'partners@rythme.store', 'Brands, dealers, teachers', 'Reply within 2 days'],
                    ] as [$icon, $title, $line1, $line2, $line3])
                        <div class="flex gap-4 rounded-3xl border border-ink/10 bg-white p-6">
                            <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                            <div>
                                <h2 class="text-sm font-bold text-ink">{{ $title }}</h2>
                                <p class="mt-1.5 text-sm font-semibold text-brand">{{ $line1 }}</p>
                                <p class="text-sm text-muted">{{ $line2 }}</p>
                                <p class="text-xs text-muted">{{ $line3 }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="rounded-3xl bg-ink p-6 text-white">
                        <h2 class="text-sm font-bold">Prefer WhatsApp?</h2>
                        <p class="mt-1.5 text-sm text-white/60">Message us photos of your gear — we love a good setup question.</p>
                        <a href="tel:+919876543210" class="mt-4 inline-block rounded-full bg-brand px-6 py-2.5 text-xs font-bold text-white transition hover:bg-brand-dark">
                            Chat on WhatsApp
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
