@php
    $sec = $homeSections['deals'] ?? null;
    $deals = $homepage['dealsOfDay'] ?? collect();
@endphp

{{-- ============================================================
     DEALS OF THE DAY — sale cards with stock meter + countdown
     Countdown runs to local midnight (daily reset), one shared
     timer feeds every card.
     ============================================================ --}}
<section class="deal-mm" aria-label="Deals of the day">
    <div class="deal-mm__inner">
        <h2 class="deal-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Deals Of The Day @endif
        </h2>

        <div class="deal-mm__grid">
            @foreach($deals as $product)
                @php
                    $image = $product->heroImage();
                    $available = max((int) $product->stock, 0);
                    $sold = (($product->id * 13) % 45) + 5; // deterministic demo counter until order data exists
                    $pct = $available + $sold > 0 ? (int) round($sold / ($available + $sold) * 100) : 0;
                    $href = route('product.show', $product->slug);
                @endphp
                <article class="pcard dealcard">
                    <div class="pcard__media">
                        <span class="pcard__badge">Sale!</span>
                        <a href="{{ $href }}" class="pcard__img" aria-label="{{ $product->name }}" tabindex="-1">
                            <img src="{{ $image }}" alt="{{ $product->name }}" width="600" height="600" loading="lazy" decoding="async">
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

                        <div class="dealcard__stock">
                            <span>Available: <b>{{ $available }}</b></span>
                            <span>Sold: <b>{{ $sold }}</b></span>
                        </div>
                        <div class="dealcard__bar" role="presentation">
                            <span style="width: {{ $pct }}%"></span>
                        </div>

                        <div class="dealcard__timer" data-deal-timer aria-label="Deal ends in">
                            <span><b data-t="d">0</b>Days</span>
                            <span><b data-t="h">0</b>Hours</span>
                            <span><b data-t="m">0</b>Mins</span>
                            <span><b data-t="s">0</b>Secs</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<script>
    // Shared daily countdown — all deals end at local midnight.
    (function () {
        var timers = document.querySelectorAll('[data-deal-timer]');
        if (!timers.length) return;
        function tick() {
            var now = new Date();
            var end = new Date(now); end.setHours(24, 0, 0, 0);
            var s = Math.max(0, Math.floor((end - now) / 1000));
            var v = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 };
            timers.forEach(function (t) {
                ['d', 'h', 'm', 's'].forEach(function (k) {
                    t.querySelector('[data-t="' + k + '"]').textContent = v[k];
                });
            });
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
