{{-- Category discovery banners — only active categories that have products (C5). --}}
@php
    $banners = collect($homepage['popularCategories'] ?? [])
        ->filter(fn (array $cat): bool => ($cat['count'] ?? 0) > 0)
        ->take(3)
        ->values();
@endphp

@if($banners->isNotEmpty())
<section class="catban-mm" aria-label="Shop by collection">
    <div class="catban-mm__inner">
        @foreach($banners as $banner)
            @php
                // `$banner['image']` already resolves: stored `icon_url` column (M-7) ->
                // Media Library `'icon'` -> committed `public/images/categories/{slug}.jpg` -> null.
                // Never fall back to an unchecked `asset('images/categories/{slug}.jpg')`,
                // which would request a 404 image for categories without a committed file.
                $bg = filled($banner['image'] ?? null) ? $banner['image'] : null;
            @endphp
            <a href="{{ route('shop.index', ['category' => $banner['slug']]) }}" class="catban-mm__card"
               @if($bg) style="background-image:url('{{ $bg }}')" @endif>
                <span class="catban-mm__scrim" aria-hidden="true"></span>
                <span class="catban-mm__content">
                    <span class="catban-mm__kicker">{{ $banner['count'] }} {{ \Illuminate\Support\Str::plural('product', $banner['count']) }}</span>
                    <span class="catban-mm__name">{{ $banner['name'] }}</span>
                    <span class="catban-mm__cta">Shop now <span aria-hidden="true">&rarr;</span></span>
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif
