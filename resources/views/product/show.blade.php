@extends('layouts.app')

@section('title', ($product->meta_title ?: $product->name . ' — Buy Online in India | Rythme Music Store'))
@section('meta_description', $product->meta_description ?: $product->short_description)

@push('head')
    {{-- Product structured data (JSON-LD) --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $product->heroImage() ?: asset('images/hero/grid-slide-guitar.jpg'),
        'description' => $product->short_description,
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => $product->brand?->name ?? 'Rythme'],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('product.show', $product),
            'priceCurrency' => 'INR',
            'price' => $product->price,
            'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12 lg:px-12">
            {{-- Breadcrumbs --}}
            <nav aria-label="Breadcrumb" class="mb-8 flex flex-wrap items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
                <span aria-hidden="true" class="text-ink/30">/</span>
                <a href="{{ route('shop.index') }}" class="transition hover:text-brand">Shop</a>
                @if($product->category?->parent)
                    <span aria-hidden="true" class="text-ink/30">/</span>
                    <a href="{{ route('shop.index', ['category' => $product->category->parent->slug]) }}" class="transition hover:text-brand">{{ $product->category->parent->name }}</a>
                @endif
                @if($product->category)
                    <span aria-hidden="true" class="text-ink/30">/</span>
                    <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="transition hover:text-brand">{{ $product->category->name }}</a>
                @endif
                <span aria-hidden="true" class="text-ink/30">/</span>
                <span class="max-w-[200px] truncate font-semibold text-ink sm:max-w-none" aria-current="page">{{ $product->name }}</span>
            </nav>

            {{-- Hero grid: gallery | buy box --}}
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)] lg:gap-14">
                {{-- ===== GALLERY ===== --}}
                <div x-data="{ active: 0, images: {{ json_encode($product->getMedia('gallery')->map(fn ($m) => $m->getUrl())->values()->all() ?: [null]) }} }">
                    <div class="relative aspect-square overflow-hidden rounded-3xl border border-ink/10 bg-white">
                        <template x-for="(img, i) in images" :key="i">
                            <div x-show="active === i" x-transition.opacity.duration.300 class="absolute inset-0 flex items-center justify-center p-8 sm:p-12">
                                <img x-show="img" :src="img" :alt="$el.closest('div').parentElement?.dataset?.name ?? '{{ $product->name }}'"
                                     class="h-full w-full object-contain" loading="lazy" decoding="async">
                                <div x-show="!img"
                                     class="flex h-full w-full flex-col items-center justify-center gap-4 rounded-3xl bg-gradient-to-br from-paper-dark via-paper to-paper-dark">
                                    <svg class="h-20 w-20 text-brand/25" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3" />
                                    </svg>
                                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-muted">{{ $product->brand?->name ?? 'Rythme' }}</p>
                                    <p class="px-10 text-center text-sm text-muted">Product photo arriving soon — admin media library se upload hoti hi yahan dikhegi.</p>
                                </div>
                            </div>
                        </template>

                        @if($product->discountPercent() > 0)
                            <span class="absolute left-5 top-5 z-10 rounded-full bg-brand px-3.5 py-1.5 text-xs font-bold text-white shadow-md">{{ $product->discountPercent() }}% off</span>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if($product->getMedia('gallery')->count() > 1)
                        <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
                            @foreach($product->getMedia('gallery') as $i => $media)
                                <button type="button" @click="active = {{ $i }}"
                                        class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border-2 transition {{ $loop->first ? 'border-brand' : 'border-ink/10 hover:border-brand/40' }}"
                                        :class="active === {{ $i }} ? 'border-brand' : 'border-ink/10'"
                                        aria-label="View image {{ $i + 1 }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $product->name }} — image {{ $i + 1 }}" class="h-full w-full object-cover" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ===== BUY BOX ===== --}}
                <div>
                    @if($product->brand)
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-muted">{{ $product->brand->name }}</p>
                    @endif

                    <h1 class="mt-3 font-playfair text-3xl font-bold leading-tight text-ink sm:text-4xl lg:text-[2.75rem]">
                        {{ $product->name }}
                    </h1>

                    {{-- Rating line --}}
                    <div class="mt-4 flex items-center gap-2.5 text-sm">
                        <span class="inline-flex items-center gap-1 rounded-md bg-brand px-2 py-0.5 text-xs font-bold text-white">
                            4.8 <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </span>
                        <span class="text-muted">Verified buyers rating</span>
                    </div>

                    {{-- Livewire price box + variant + qty + add-to-cart --}}
                    <livewire:add-to-cart :product="$product" :key="'atc-' . $product->id" />

                    <div class="mt-4">
                        <livewire:wishlist-button :product-id="$product->id" :variant="'page'"
                                                  wire:key="wl-page-{{ $product->id }}" />
                    </div>

                    {{-- Trust strip --}}
                    <div class="mt-8 grid grid-cols-3 gap-3 rounded-2xl border border-ink/10 bg-white p-4 text-center">
                        <div class="flex flex-col items-center gap-1.5">
                            <svg class="h-6 w-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" /></svg>
                            <span class="text-[11px] font-semibold leading-tight text-ink">1-Year Warranty</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <svg class="h-6 w-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6 0a2 2 0 11-4 0m4 0a2 2 0 104 0m-4 0h4" /></svg>
                            <span class="text-[11px] font-semibold leading-tight text-ink">Free Shipping</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <svg class="h-6 w-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h2m4 0h4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <span class="text-[11px] font-semibold leading-tight text-ink">EMI Available</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TABS: Description / Specs ===== --}}
            <div class="mt-16" x-data="{ tab: 'description' }">
                <div class="flex gap-2 border-b border-ink/10" role="tablist" aria-label="Product information">
                    <button type="button" role="tab" :aria-selected="tab === 'description' ? 'true' : 'false'"
                            @click="tab = 'description'"
                            class="-mb-px border-b-2 px-5 py-3 text-sm font-bold transition {{ $product->description ? '' : 'pointer-events-none opacity-40' }}"
                            :class="tab === 'description' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink'">
                        Description
                    </button>
                    <button type="button" role="tab" :aria-selected="tab === 'specs' ? 'true' : 'false'"
                            @click="tab = 'specs'"
                            class="-mb-px border-b-2 px-5 py-3 text-sm font-bold transition"
                            :class="tab === 'specs' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink'">
                        Specifications
                    </button>
                </div>

                <div x-show="tab === 'description'" x-transition.opacity.duration.200 class="prose-sm max-w-3xl py-8 leading-7 text-ink/80">
                    {!! $product->description !!}
                </div>

                <div x-show="tab === 'specs'" x-cloak x-transition.opacity.duration.200 class="max-w-3xl py-8">
                    <dl class="divide-y divide-ink/10 rounded-2xl border border-ink/10 bg-white">
                        <div class="flex items-center justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-muted">SKU</dt>
                            <dd class="text-sm font-semibold text-ink">{{ $product->sku }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-muted">Brand</dt>
                            <dd class="text-sm font-semibold text-ink">{{ $product->brand?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-muted">Category</dt>
                            <dd class="text-sm font-semibold text-ink">{{ $product->category?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-muted">Condition</dt>
                            <dd class="text-sm font-semibold text-ink">Brand New</dd>
                        </div>
                        <div class="flex items-center justify-between gap-6 px-6 py-4">
                            <dt class="text-sm text-muted">Warranty</dt>
                            <dd class="text-sm font-semibold text-ink">1 Year</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- ===== REVIEWS ===== --}}
            <livewire:review-section :product="$product" :key="'rev-' . $product->id" />

            {{-- ===== RELATED ===== --}}
            @if($related->isNotEmpty())
                <section aria-label="Related products" class="mt-20">
                    <div class="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p class="section-kicker mb-3">Complete the setup</p>
                            <h2 class="font-playfair text-2xl font-bold text-ink sm:text-3xl">You may also like</h2>
                        </div>
                        <a href="{{ route('shop.index', $product->category ? ['category' => $product->category->slug] : []) }}"
                           class="text-link text-sm">
                            View all <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-4">
                        @foreach($related as $relatedProduct)
                            <x-shop-card :product="$relatedProduct" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
