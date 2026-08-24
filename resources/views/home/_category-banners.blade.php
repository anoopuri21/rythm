{{-- ============================================================
     CATEGORY BANNERS — three wide cards with text overlay
     ============================================================ --}}
<section class="catban-mm" aria-label="Shop by collection">
    <div class="catban-mm__inner">
        @foreach([
            ['img' => 'images/categories/pro-audio.jpg', 'kicker' => 'Record like a pro', 'title' => 'Studio & Recording', 'slug' => 'pro-audio'],
            ['img' => 'images/categories/drums-percussion.jpg', 'kicker' => 'Feel every beat', 'title' => 'Drums & Percussion', 'slug' => 'drums-percussion'],
            ['img' => 'images/categories/accessories.jpg', 'kicker' => 'Complete your rig', 'title' => 'Accessories', 'slug' => 'accessories'],
        ] as $banner)
            <a href="/shop?category={{ $banner['slug'] }}" class="catban-mm__card"
               style="background-image:url('{{ asset($banner['img']) }}')">
                <span class="catban-mm__scrim" aria-hidden="true"></span>
                <span class="catban-mm__content">
                    <span class="catban-mm__kicker">{{ $banner['kicker'] }}</span>
                    <span class="catban-mm__name">{{ $banner['title'] }}</span>
                    <span class="catban-mm__cta">Shop now <span aria-hidden="true">&rarr;</span></span>
                </span>
            </a>
        @endforeach
    </div>
</section>
