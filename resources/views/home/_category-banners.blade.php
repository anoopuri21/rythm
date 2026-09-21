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
                $bg = filled($banner['image'] ?? null)
                    ? $banner['image']
                    : asset('images/categories/'.($banner['slug'] ?? 'accessories').'.jpg');
                // Ensure relative public path works when image is already "/images/..."
                if (is_string($bg) && str_starts_with($bg, '/')) {
                    $bgStyle = $bg;
                } else {
                    $bgStyle = $bg;
                }
            @endphp
            <a href="{{ route('shop.index', ['category' => $banner['slug']]) }}" class="catban-mm__card"
               style="background-image:url('{{ $bgStyle }}')">
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
