@php $sec = $homeSections['new-arrivals'] ?? null; @endphp
@php
    $featured = config('catalog.featured');
    // Reference-style clean grid: editor pick + fresh arrivals
    $arrivals = [$featured[0], $featured[7], $featured[5], $featured[6]];
@endphp

{{-- ============================================================
     NEW ARRIVALS — reference minimal-tech grid
     White section · 4 clean product cards · view-all link
     ============================================================ --}}
<section id="new-arrivals" class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="reveal-section mb-12 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="section-kicker">{{ $sec->kicker ?? 'Fresh from the flight case' }}</p>
                <h2 class="section-title">@if($sec?->title){{ $sec->title }}@if($sec?->title_accent) <em>{{ $sec->title_accent }}</em>@endif@else Meet the <em>new arrivals.</em>@endif</h2>
                <p class="mt-4 max-w-xl text-sm leading-6 text-rythme-warm-gray">
                    Newly stocked instruments, set up and ready to play.
                </p>
            </div>
            <a href="/shop?sort=newest" class="text-link">See everything new <span aria-hidden="true">↗</span></a>
        </div>

        <div class="min-grid grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($arrivals as $product)
                <x-minimal-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
