@php
    /**
     * Floating WhatsApp button — number/message are managed by the admin
     * (Filament → Settings → Contact & address). Empty number hides it.
     */
    $waSettings = app(\App\Services\SiteSettingsService::class);
    $waNumber = trim((string) ($waSettings->get('whatsapp_number') ?? ''));
    $waDigits = preg_replace('/\D+/', '', $waNumber);
    $waMessage = trim((string) ($waSettings->get('whatsapp_message') ?? ''));
@endphp

@if($waDigits !== '')
    <a href="https://wa.me/{{ $waDigits }}{{ $waMessage !== '' ? '?text=' . rawurlencode($waMessage) : '' }}"
       target="_blank"
       rel="noopener noreferrer"
       class="whatsapp-float"
       aria-label="Chat with us on WhatsApp">
        <svg viewBox="0 0 32 32" class="h-7 w-7" fill="currentColor" aria-hidden="true">
            <path d="M16.003 3.2c-7.06 0-12.8 5.74-12.8 12.8 0 2.257.594 4.463 1.72 6.404L3.2 28.8l6.55-1.7a12.74 12.74 0 006.253 1.634h.005c7.06 0 12.8-5.74 12.8-12.8 0-3.42-1.332-6.635-3.75-9.053A12.71 12.71 0 0016.003 3.2zm0 23.04h-.004a10.63 10.63 0 01-5.42-1.484l-.389-.231-4.028 1.045 1.075-3.927-.253-.403a10.6 10.6 0 01-1.626-5.66c0-5.867 4.777-10.64 10.649-10.64 2.843 0 5.515 1.108 7.524 3.12a10.57 10.57 0 013.117 7.527c-.002 5.868-4.778 10.653-10.645 10.653zm5.84-7.976c-.32-.16-1.893-.934-2.186-1.04-.293-.107-.507-.16-.72.16s-.826 1.04-1.013 1.253c-.187.213-.373.24-.693.08-.32-.16-1.351-.498-2.573-1.588-.951-.848-1.593-1.895-1.78-2.215-.186-.32-.02-.493.14-.652.144-.144.32-.374.48-.561.16-.187.213-.32.32-.534.107-.213.053-.4-.027-.56-.08-.16-.72-1.735-.986-2.375-.26-.624-.524-.54-.72-.55l-.613-.011a1.18 1.18 0 00-.853.4c-.293.32-1.12 1.094-1.12 2.669s1.147 3.095 1.307 3.309c.16.213 2.257 3.446 5.468 4.832.764.33 1.36.527 1.825.674.767.244 1.464.21 2.016.127.615-.092 1.893-.774 2.16-1.521.267-.747.267-1.387.187-1.52-.08-.134-.293-.214-.613-.374z"/>
        </svg>
    </a>
@endif
