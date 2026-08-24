<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
    <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
        <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
        <span aria-hidden="true" class="text-ink/30">/</span>
        <span class="font-semibold text-ink" aria-current="page">Wishlist</span>
    </nav>

    <p class="section-kicker mb-4">Saved for later</p>
    <h1 class="section-title">Your wishlist</h1>

    @if($error)
        <p class="mt-6 max-w-xl rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ $error }}</p>
    @endif

    @if($products->isEmpty())
        <div class="mt-10 flex flex-col items-center rounded-3xl border border-dashed border-ink/15 bg-white px-6 py-24 text-center">
            <p class="text-6xl" aria-hidden="true">💛</p>
            <h2 class="mt-6 font-playfair text-2xl font-bold text-ink">Nothing saved yet</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted">
                Tap the heart on any instrument you love and it will wait for you here.
            </p>
            <a href="{{ route('shop.index') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                Browse the shop <span aria-hidden="true">→</span>
            </a>
        </div>
    @else
        <p class="mt-6 text-sm text-muted">{{ $products->count() }} {{ Str::plural('instrument', $products->count()) }} saved</p>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-3">
            @foreach($products as $product)
                <article class="group flex h-full flex-col rounded-2xl border border-ink/10 bg-white p-3.5 transition-all duration-300 hover:-translate-y-1 hover:border-brand/30 hover:shadow-[0_24px_50px_rgba(10,10,10,0.12)] sm:p-4" wire:key="wl-product-{{ $product->id }}">
                    <div class="relative aspect-square overflow-hidden rounded-xl bg-paper-dark">
                        @if($product->getFirstMediaUrl('gallery'))
                            <img src="{{ $product->getFirstMediaUrl('gallery') }}" alt="{{ $product->name }}" class="h-full w-full object-contain transition duration-700 group-hover:scale-105" loading="lazy">
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-paper-dark via-paper to-paper-dark p-6 text-center">
                                <svg class="h-10 w-10 text-brand/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 19l12-3" /></svg>
                                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-muted">{{ $product->brand?->name }}</p>
                            </div>
                        @endif
                        @if($product->discountPercent() > 0)
                            <span class="absolute left-3 top-3 rounded-full bg-brand px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">{{ $product->discountPercent() }}% off</span>
                        @endif
                        <button type="button" wire:click="remove({{ $product->id }})"
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-brand shadow-sm transition hover:bg-brand hover:text-white"
                                aria-label="Remove {{ $product->name }} from wishlist">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col px-1 pb-1 pt-4">
                        @if($product->brand)
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted">{{ $product->brand->name }}</p>
                        @endif
                        <h3 class="mt-1.5 text-sm font-semibold leading-snug text-ink">
                            <a href="{{ route('product.show', $product) }}" class="transition hover:text-brand">{{ $product->name }}</a>
                        </h3>
                        <div class="mt-auto flex flex-wrap items-baseline gap-x-2 gap-y-1 pt-3.5">
                            <span class="text-lg font-bold text-ink">₹{{ number_format((float) $product->price) }}</span>
                            @if($product->compare_at_price)
                                <span class="text-xs text-muted line-through">₹{{ number_format((float) $product->compare_at_price) }}</span>
                            @endif
                        </div>
                        <button type="button" wire:click="moveToCart({{ $product->id }})"
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-brand">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            Move to cart
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
