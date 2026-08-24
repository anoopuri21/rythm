@php
    // Single root element is REQUIRED — Livewire attaches wire:id/snapshot to
    // the root; multiple top-level elements (button + svg) used to misplace
    // them and break wire:click (click went to the parent component).
    $isPage = $variant === 'page';
@endphp

<div @class([
    $isPage ? '' : 'absolute right-3 top-3 z-20',
])>
    @if($isPage)
        <button type="button" wire:click="toggle"
                class="inline-flex h-13 items-center justify-center gap-2 rounded-full border px-7 py-3.5 text-sm font-bold transition
                {{ $active ? 'border-brand bg-brand/5 text-brand' : 'border-ink/15 bg-white text-ink hover:border-brand/50 hover:text-brand' }}"
                aria-pressed="{{ $active ? 'true' : 'false' }}"
                aria-label="{{ $active ? 'Remove from wishlist' : 'Add to wishlist' }}">
            <svg class="h-5 w-5" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>{{ $active ? 'Saved to wishlist' : 'Save to wishlist' }}</span>
        </button>
    @else
        <button type="button" wire:click="toggle"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-ink shadow-sm backdrop-blur transition hover:bg-brand hover:text-white
                {{ $active ? 'bg-brand text-white' : '' }}"
                aria-pressed="{{ $active ? 'true' : 'false' }}"
                aria-label="{{ $active ? 'Remove from wishlist' : 'Add to wishlist' }}">
            <svg class="h-5 w-5" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
    @endif
</div>
