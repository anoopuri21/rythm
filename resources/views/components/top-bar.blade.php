@php
    $phone = trim((string) config('rythme.contact_phone', ''));
    $email = trim((string) config('rythme.contact_email', ''));
    $configuredSocials = config('rythme.social_links', []);
    $socials = collect([
        'instagram' => ['label' => 'Instagram', 'url' => $configuredSocials['instagram'] ?? null],
        'facebook' => ['label' => 'Facebook', 'url' => $configuredSocials['facebook'] ?? null],
        'youtube' => ['label' => 'YouTube', 'url' => $configuredSocials['youtube'] ?? null],
    ])->filter(fn (array $social): bool => is_string($social['url']) && str_starts_with($social['url'], 'https://'));
@endphp

@if($phone !== '' || $email !== '' || $socials->isNotEmpty())
    <div class="top-bar" role="region" aria-label="Contact and social links">
        <div class="top-bar__inner">
            <div class="top-bar__contact">
                @if($phone !== '')
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="top-bar__link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0-1.243 1.007-2.25 2.25-2.25h2.04c.97 0 1.823.624 2.124 1.546l.74 2.22a2.25 2.25 0 0 1-.51 2.28l-1.24 1.24a12.04 12.04 0 0 0 5.13 5.13l1.24-1.24a2.25 2.25 0 0 1 2.28-.51l2.22.74A2.25 2.25 0 0 1 19.77 18v2.04a2.25 2.25 0 0 1-2.25 2.25C9.216 22.29 1.71 14.784 1.71 6.48c0-.068.003-.136.006-.203L2.25 6.75Z"/></svg>
                        <span>{{ $phone }}</span>
                    </a>
                @endif
                @if($email !== '')
                    <a href="mailto:{{ $email }}" class="top-bar__link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6"/></svg>
                        <span>{{ $email }}</span>
                    </a>
                @endif
            </div>

            @if($socials->isNotEmpty())
                <nav class="top-bar__socials" aria-label="Social media">
                    @foreach($socials as $network => $social)
                        <a href="{{ $social['url'] }}" class="top-bar__social" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                            @if($network === 'instagram')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></svg>
                            @elseif($network === 'facebook')
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.7 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.6 1.7-1.6h1.8V3.8c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1V10H8.2v3h2.6v8h2.9Z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.8 2.8 0 0 0-2-2C17.8 4.7 12 4.7 12 4.7s-5.8 0-7.6.5a2.8 2.8 0 0 0-2 2C1.9 9 1.9 12 1.9 12s0 3 .5 4.8a2.8 2.8 0 0 0 2 2c1.8.5 7.6.5 7.6.5s5.8 0 7.6-.5a2.8 2.8 0 0 0 2-2c.5-1.8.5-4.8.5-4.8s0-3-.5-4.8ZM10 15.8V8.2l6.3 3.8-6.3 3.8Z"/></svg>
                            @endif
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
@endif
