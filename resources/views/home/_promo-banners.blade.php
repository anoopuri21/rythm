{{-- Promo banners — DB HomepageBlock section "promo" only; hide when empty (C5). --}}
@php
    $promos = collect($homepage['promos'] ?? [])
        ->filter(fn ($block) => filled($block->title ?? null))
        ->take(2)
        ->values();
@endphp

@if($promos->isNotEmpty())
<section class="promo-mm" aria-label="Featured collections">
    <div class="promo-mm__inner">
        @foreach($promos as $i => $block)
            @php
                $href = (string) ($block->content ?? '/shop');
                if (! str_starts_with($href, '/') || str_starts_with($href, '//')) {
                    $href = '/shop';
                }
                $isDark = $i % 2 === 1;
                $bg = $isDark
                    ? asset('images/deals-banner.jpg')
                    : asset('images/story-studio.jpg');
            @endphp
            <a href="{{ $href }}"
               class="promo-mm__card {{ $isDark ? 'promo-mm__card--dark' : 'promo-mm__card--light' }}"
               style="background-image:url('{{ $bg }}')">
                <span class="promo-mm__scrim {{ $isDark ? 'promo-mm__scrim--dark' : 'promo-mm__scrim--light' }}" aria-hidden="true"></span>
                <span class="promo-mm__content">
                    @if(filled($block->subtitle))
                        <span class="promo-mm__kicker {{ $isDark ? 'promo-mm__kicker--gold' : '' }}">{{ $block->subtitle }}</span>
                    @endif
                    <span class="promo-mm__title {{ $isDark ? 'promo-mm__title--light' : '' }}">{!! nl2br(e($block->title)) !!}</span>
                    <span class="promo-mm__cta {{ $isDark ? 'promo-mm__cta--light' : '' }}">Explore <span aria-hidden="true">&rarr;</span></span>
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif
