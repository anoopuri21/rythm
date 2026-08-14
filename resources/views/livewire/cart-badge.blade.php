<span class="relative inline-flex h-10 w-10 items-center justify-center rounded-full transition-colors duration-300 hover:bg-black/5"
      wire:poll.5s="refresh" aria-hidden="true">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
    </svg>
    @if($count > 0)
        <span class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white transition-transform"
              wire:transition.scale.origin.top>{{ $count }}</span>
    @endif
</span>
