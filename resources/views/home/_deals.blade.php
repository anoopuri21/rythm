@php
    $sec = $homeSections['best-deals'] ?? ($homeSections['deals'] ?? null);
    $deals = $homepage['bestDeals'] ?? collect();
@endphp

@if($deals->isNotEmpty())
{{-- ============================================================
     BEST DEALS — active products with a truthful compare-at discount.
     Unsupported sales counters and countdowns are intentionally omitted;
     expiry UI may return only with a persisted timestamp.
     ============================================================ --}}
<section id="best-deals" class="deal-mm" aria-label="Best deals">
    <div class="deal-mm__inner">
        <h2 class="deal-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Best Deals @endif
        </h2>

        <div class="deal-mm__grid">
            @foreach($deals as $product)
                @php
                    $image = $product->heroImage();
                    $available = max((int) $product->stock, 0);
                    $href = route('product.show', $product->slug);
                @endphp
                <article class="pcard dealcard">
                    <div class="pcard__media">
                        <span class="pcard__badge">Sale!</span>
                        <a href="{{ $href }}" class="pcard__img" aria-label="{{ $product->name }}" tabindex="-1">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $product->name }}" width="600" height="600" loading="lazy" decoding="async">
                            @else
                                <span class="pcard__img-fallback" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3"/></svg>
                                </span>
                            @endif
                        </a>
                        <a href="{{ $href }}" class="pcard__view">View product</a>
                    </div>
                    <div class="pcard__body">
                        @if($product->category)
                            <a href="/shop?category={{ $product->category->slug }}" class="pcard__cat">{{ $product->category->name }}</a>
                        @endif
                        <h3 class="pcard__name"><a href="{{ $href }}">{{ $product->name }}</a></h3>
                        <p class="pcard__price">
                            <del>₹{{ number_format((float) $product->compare_at_price) }}</del>
                            <ins>₹{{ number_format((float) $product->price) }}</ins>
                        </p>

                        <p class="dealcard__stock" aria-label="Availability">
                            @if($available > 0)
                                <span>Available now: <b>{{ $available }}</b></span>
                            @else
                                <span><b>Currently out of stock</b></span>
                            @endif
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
