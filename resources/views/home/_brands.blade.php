@php
    $sec = $homeSections['brands'] ?? null;
    $brands = $homepage['brandNames'] ?? collect();
@endphp

@if($brands->isNotEmpty())
{{-- ============================================================
     POPULAR BRANDS — wordmark strip (mega-market logo row)
     ============================================================ --}}
<section class="brand-mm" aria-label="Popular brands">
    <div class="brand-mm__inner">
        <h2 class="brand-mm__title">
            @if($sec?->title){{ $sec->title }}@if($sec?->title_accent) {{ $sec->title_accent }}@endif
            @else Popular Brands @endif
        </h2>

        <ul class="brand-mm__strip">
            @foreach($brands as $name)
                <li><a href="/shop?brand%5B%5D={{ Str::slug($name) }}">{{ $name }}</a></li>
            @endforeach
        </ul>
    </div>
</section>
@endif
