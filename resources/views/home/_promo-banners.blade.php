{{-- ============================================================
     PROMO BANNERS — two wide side-by-side banners (mega-market)
     Left: light studio banner · Right: dark stage banner
     ============================================================ --}}
<section class="promo-mm" aria-label="Featured promotions">
    <div class="promo-mm__inner">
        <a href="/shop?category=pro-audio" class="promo-mm__card promo-mm__card--light"
           style="background-image:url('{{ asset('images/story-studio.jpg') }}')">
            <span class="promo-mm__scrim promo-mm__scrim--light" aria-hidden="true"></span>
            <span class="promo-mm__content">
                <span class="promo-mm__kicker">Studio &amp; Recording</span>
                <span class="promo-mm__title">Experience<br>Sound</span>
                <span class="promo-mm__cta">Discover <span aria-hidden="true">&rarr;</span></span>
            </span>
        </a>

        <a href="/shop?category=electric-guitars" class="promo-mm__card promo-mm__card--dark"
           style="background-image:url('{{ asset('images/deals-banner.jpg') }}')">
            <span class="promo-mm__scrim promo-mm__scrim--dark" aria-hidden="true"></span>
            <span class="promo-mm__content">
                <span class="promo-mm__kicker promo-mm__kicker--gold">Electric Guitars</span>
                <span class="promo-mm__title promo-mm__title--light">Stage-Ready<br>Tone</span>
                <span class="promo-mm__cta promo-mm__cta--light">Shop now <span aria-hidden="true">&rarr;</span></span>
            </span>
        </a>
    </div>
</section>
