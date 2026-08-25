{{-- Count-only badge — the parent .nav__icon supplies the cart icon.
     Root span always renders (Livewire single root); hidden when empty. --}}
<span class="nav__badge {{ $count > 0 ? '' : 'is-empty' }}" wire:poll.5s="refresh">{{ $count }}</span>
