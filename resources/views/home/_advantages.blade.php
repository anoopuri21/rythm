@php $sec = $homeSections['why-rythme'] ?? null; @endphp

{{-- ============================================================
     OUR ADVANTAGES — 8 text items, 4-col grid (mega-market)
     ============================================================ --}}
<section class="adv-mm" aria-label="Our advantages">
    <div class="adv-mm__inner">
        <h2 class="adv-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Our Advantages @endif
        </h2>

        <div class="adv-mm__grid">
            @foreach([
                ['t' => 'Instrument-First Catalogue', 'd' => 'Browse a focused catalogue of guitars, keys, drums, studio gear and accessories.'],
                ['t' => 'Category-Guided Discovery', 'd' => 'Move from instrument families to the exact product type you need.'],
                ['t' => 'Useful Brand Filters', 'd' => 'Compare the available catalogue by brand without leaving the Shop.'],
                ['t' => 'Stock-Aware Shopping', 'd' => 'Current product and variant availability is checked before an order is placed.'],
                ['t' => 'Server-Verified Totals', 'd' => 'Prices, discounts and order totals are recalculated by the application at checkout.'],
                ['t' => 'Wishlist Shortlisting', 'd' => 'Save instruments to your account and return to them when you are ready.'],
                ['t' => 'Account Address Book', 'd' => 'Keep delivery addresses together with your protected customer account.'],
                ['t' => 'Order Status Tracking', 'd' => 'Follow confirmed order-status updates from your account or a valid signed link.'],
            ] as $i => $item)
                <div class="adv-mm__item">
                    <span class="adv-mm__num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="adv-mm__item-title">{{ $item['t'] }}</h3>
                    <p class="adv-mm__item-desc">{{ $item['d'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
