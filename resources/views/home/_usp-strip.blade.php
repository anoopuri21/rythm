{{-- ============================================================
     USP / BENEFITS STRIP — Admin → Homepage blocks → "Why Rythme (USPs)".
     Nothing here is hardcoded: title, copy, icon and order all come from
     `homepage_blocks`. The whole section hides when no row is active.
     Desktop: single row with dividers · Mobile: 2-col grid.
     ============================================================ --}}
@php
    $usps = collect($homepage['usps'] ?? [])
        ->filter(fn ($block): bool => filled($block->title ?? null))
        ->values();
@endphp

@if($usps->isNotEmpty())
<section class="usp-strip" aria-label="Why shop with us">
    <div class="usp-strip__inner">
        @foreach($usps as $usp)
            <div class="usp-strip__item">
                <x-ui.icon :name="$usp->icon" />
                <p><strong>{{ $usp->title }}</strong> {{ $usp->content }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif
