@php $sec = $homeSections['new-arrivals'] ?? null; @endphp
@php
    // NEW ARRIVALS — admin-driven: latest active products (created_at desc)
    $arrivals = $homepage['newArrivals'] ?? collect();
    $arrivals = $arrivals->take(8);
@endphp

{{-- ============================================================
     NEW ARRIVALS — admin-driven clean grid
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

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($arrivals as $product)
                <x-minimal-product-card :product="$product" />
            @empty
                <p class="text-sm text-rythme-warm-gray">No products yet — add products in admin.</p>
            @endforelse
        </div>
    </div>
</section>
