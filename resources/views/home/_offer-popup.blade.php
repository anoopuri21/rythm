@php
    $popupOffer = collect($homepage['bestDeals'] ?? [])
        ->map(function ($product): ?array {
            $compareAt = (float) ($product->compare_at_price ?? 0);
            $price = (float) ($product->price ?? 0);

            if ($compareAt <= 0 || $price <= 0 || $compareAt <= $price) {
                return null;
            }

            $discount = (int) floor((($compareAt - $price) / $compareAt) * 100);

            return $discount >= 10 && $discount <= 50
                ? [
                    'product' => $product,
                    'discount' => $discount,
                    'image' => $product->thumbnailImage(),
                    'price' => number_format($price),
                    'compare_at' => number_format($compareAt),
                ]
                : null;
        })
        ->filter()
        ->first();
@endphp

@if($popupOffer)
    <div
        class="offer-popup is-pending"
        data-offer-popup
        data-offer-popup-storage-key="rythme-offer-popup-closed-at-v1"
        aria-hidden="true"
    >
        <div class="offer-popup__backdrop" aria-hidden="true"></div>
        <div
            class="offer-popup__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="offer-popup-title"
            aria-describedby="offer-popup-description"
        >
            <button type="button" class="offer-popup__close" data-offer-popup-close aria-label="Close offer" title="Close offer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>

            <div class="offer-popup__visual" aria-hidden="true">
                @if($popupOffer['image'])
                    <img src="{{ $popupOffer['image'] }}" alt="" width="720" height="720" loading="eager" decoding="async">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3"/></svg>
                @endif
            </div>

            <div class="offer-popup__content">
                <p class="offer-popup__eyebrow">Selected offer</p>
                <p class="offer-popup__discount">{{ $popupOffer['discount'] }}% off</p>
                <h2 id="offer-popup-title" class="offer-popup__title">Make room for more music.</h2>
                <p id="offer-popup-description" class="offer-popup__copy">A current product offer, calculated from its stored price and compare-at price.</p>
                <p class="offer-popup__product">{{ $popupOffer['product']->name }}</p>
                <p class="offer-popup__price">
                    <strong>₹{{ $popupOffer['price'] }}</strong>
                    <del>₹{{ $popupOffer['compare_at'] }}</del>
                </p>
                <a href="{{ route('product.show', $popupOffer['product']->slug) }}" class="offer-popup__cta">View this offer <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
@endif
