@php
    $offers = collect($homepage['bestDeals'] ?? [])
        ->map(function ($product): ?array {
            $compareAt = (float) ($product->compare_at_price ?? 0);
            $price = (float) $product->price;

            if ($compareAt <= 0 || $price <= 0 || $compareAt <= $price) {
                return null;
            }

            return [
                'product' => $product,
                'discount' => (int) floor((($compareAt - $price) / $compareAt) * 100),
            ];
        })
        ->filter(fn (?array $offer): bool => $offer !== null && $offer['discount'] >= 10 && $offer['discount'] <= 50)
        ->take(8)
        ->values();
@endphp

@if($offers->isNotEmpty())
    <section class="offer-marquee" aria-label="Selected product offers">
        <div class="offer-marquee__inner">
            <span class="offer-marquee__label">Selected offers</span>
            <div class="offer-marquee__viewport">
                <div class="offer-marquee__track">
                    <div class="offer-marquee__set">
                        @foreach($offers as $offer)
                            <a href="{{ route('product.show', $offer['product']->slug) }}" class="offer-marquee__item">
                                <strong>{{ $offer['discount'] }}% OFF</strong>
                                <span>{{ $offer['product']->name }}</span>
                                <span aria-hidden="true">→</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="offer-marquee__set" aria-hidden="true">
                        @foreach($offers as $offer)
                            <span class="offer-marquee__item">
                                <strong>{{ $offer['discount'] }}% OFF</strong>
                                <span>{{ $offer['product']->name }}</span>
                                <span aria-hidden="true">→</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
