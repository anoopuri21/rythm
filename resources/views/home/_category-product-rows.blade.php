@php
    $rows = $homepage['categoryRows'] ?? collect();
@endphp

@if($rows->isNotEmpty())
<div id="category-product-rows" aria-label="Shop products by category">
    @foreach($rows as $item)
        @php
            $row = $item['row'];
            $category = $item['category'];
            $products = $item['products'];
            $headingId = 'category-row-' . $category->slug;
        @endphp

        @if($products->isNotEmpty())
            <section class="prod-mm" aria-labelledby="{{ $headingId }}">
                <div class="prod-mm__inner">
                    <div class="mb-7 flex items-end justify-between gap-4 sm:mb-9">
                        <h2 id="{{ $headingId }}" class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                            {{ filled($row->title) ? $row->title : $category->name }}
                        </h2>
                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                           class="shrink-0 text-sm font-bold text-brand hover:text-brand-dark"
                           aria-label="Browse all {{ $category->name }} products">
                            View all <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <div class="prod-mm__grid">
                        @foreach($products as $product)
                            <x-mega-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endforeach
</div>
@endif
