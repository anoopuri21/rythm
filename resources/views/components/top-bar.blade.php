@php
    /**
     * Site top bar (sits directly above #navbar).
     * Every value here is admin-managed: Filament → Settings → "Contact & address"
     * and "Social links". Blank values are simply not rendered.
     */
    $siteSettings = app(\App\Services\SiteSettingsService::class);

    $phone = trim((string) ($siteSettings->get('contact_phone') ?? ''));
    $email = trim((string) ($siteSettings->get('contact_email') ?? ''));

    $socials = collect([
        'instagram' => ['label' => 'Instagram', 'url' => $siteSettings->get('social_instagram')],
        'facebook' => ['label' => 'Facebook', 'url' => $siteSettings->get('social_facebook')],
        'youtube' => ['label' => 'YouTube', 'url' => $siteSettings->get('social_youtube')],
        'x' => ['label' => 'X (Twitter)', 'url' => $siteSettings->get('social_x')],
        'linkedin' => ['label' => 'LinkedIn', 'url' => $siteSettings->get('social_linkedin')],
    ])->filter(function (array $social): bool {
        $url = trim((string) ($social['url'] ?? ''));

        // Only render an icon when the admin has saved a valid absolute URL.
        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false
            && (str_starts_with($url, 'https://') || str_starts_with($url, 'http://'));
    })->map(function (array $social): array {
        $social['url'] = trim((string) $social['url']);

        return $social;
    });
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
                            @elseif($network === 'youtube')
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.8 2.8 0 0 0-2-2C17.8 4.7 12 4.7 12 4.7s-5.8 0-7.6.5a2.8 2.8 0 0 0-2 2C1.9 9 1.9 12 1.9 12s0 3 .5 4.8a2.8 2.8 0 0 0 2 2c1.8.5 7.6.5 7.6.5s5.8 0 7.6-.5a2.8 2.8 0 0 0 2-2c.5-1.8.5-4.8.5-4.8s0-3-.5-4.8ZM10 15.8V8.2l6.3 3.8-6.3 3.8Z"/></svg>
                            @elseif($network === 'x')
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.53 3h3.02l-6.6 7.54L21.75 21h-5.9l-4.62-6.04L5.94 21H2.92l7.06-8.07L2.5 3h6.05l4.18 5.52L17.53 3Zm-1.06 16.17h1.67L7.6 4.74H5.81l10.66 14.43Z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 5.5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0ZM3.3 8.98h3.4V21H3.3V8.98Zm5.53 0h3.26v1.64h.05c.45-.86 1.57-1.77 3.23-1.77 3.45 0 4.09 2.27 4.09 5.22V21h-3.4v-5.35c0-1.28-.02-2.92-1.78-2.92-1.78 0-2.05 1.39-2.05 2.83V21h-3.4V8.98Z"/></svg>
                            @endif
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
@endif
