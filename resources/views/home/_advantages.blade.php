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
                ['t' => 'Fee-Free EMI', 'd' => 'No-cost instalments on all major cards and UPI apps.'],
                ['t' => 'Convenient Delivery', 'd' => 'Doorstep delivery across India with instrument-safe packing.'],
                ['t' => 'Best Price Guarantee', 'd' => 'Found it cheaper? We match genuine dealer prices.'],
                ['t' => 'Expert Setup & Services', 'd' => 'Pro setup, servicing and repairs by trained luthiers.'],
                ['t' => 'Rythme Rewards', 'd' => 'Earn bonus points on every order, redeem on your next one.'],
                ['t' => 'Express Delivery', 'd' => 'Same-day dispatch in metro cities on in-stock gear.'],
                ['t' => 'Store Pickup In 15 Min', 'd' => 'Order online, collect from our Delhi store in minutes.'],
                ['t' => 'Old Gear Trade-In', 'd' => 'Exchange your old instrument for instant credit.'],
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
