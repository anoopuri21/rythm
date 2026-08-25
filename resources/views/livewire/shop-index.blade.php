<div class="mx-auto max-w-[1520px] px-4 py-8 sm:px-6 lg:px-8" x-data="{ mobileFilters: false }"
     @keydown.escape.window="if (mobileFilters) { mobileFilters = false; $nextTick(() => $refs.filterTrigger.focus()) }">
    @if($catalogHasProducts && $categories !== [])
    <section class="mb-8 border-b border-ink/10 pb-8" aria-labelledby="shop-shortcuts-title">
        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand">Start with a sound</p>
                <h2 id="shop-shortcuts-title" class="mt-1 text-xl font-extrabold text-ink sm:text-2xl">Shop popular categories</h2>
            </div>
            @if($category !== null)
                <button type="button" wire:click="setCategory(null)" class="text-sm font-bold text-brand underline underline-offset-4">View all</button>
            @endif
        </div>
        <div class="shop-shortcuts" role="list">
            @foreach(array_slice($categories, 0, 8) as $shortcut)
                <button type="button" role="listitem" wire:click="setCategory('{{ $shortcut['slug'] }}')"
                        class="shop-shortcut {{ $category === $shortcut['slug'] ? 'is-active' : '' }}"
                        aria-pressed="{{ $category === $shortcut['slug'] ? 'true' : 'false' }}">
                    <span class="shop-shortcut__image">
                        <img src="{{ asset('images/categories/'.$shortcut['slug'].'.jpg') }}" alt="" width="160" height="160" loading="lazy" decoding="async">
                    </span>
                    <span>{{ $shortcut['name'] }}</span>
                </button>
            @endforeach
        </div>
    </section>
    @endif

    <div class="{{ $catalogHasProducts ? 'lg:grid lg:grid-cols-[260px_minmax(0,1fr)] lg:gap-8 xl:gap-10' : '' }}">

        {{-- ===== FILTER SIDEBAR ===== --}}
        @if($catalogHasProducts)
        {{-- Mobile overlay --}}
        <div x-cloak x-show="mobileFilters" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm lg:hidden"
             @click="mobileFilters = false; $nextTick(() => $refs.filterTrigger.focus())" aria-hidden="true"></div>

        <aside id="shop-filter-panel" x-cloak x-trap.inert.noscroll="mobileFilters"
               :class="mobileFilters ? 'translate-x-0 visible' : '-translate-x-full invisible lg:visible'"
               x-transition:enter="transition ease-out duration-300"
               x-transition:leave="transition ease-in duration-200"
               class="fixed inset-y-0 left-0 z-[70] w-[86%] max-w-sm overflow-y-auto bg-paper p-6 shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:w-auto lg:max-w-none lg:translate-x-0 lg:overflow-visible lg:bg-transparent lg:p-0 lg:shadow-none"
               :role="mobileFilters ? 'dialog' : 'region'" :aria-modal="mobileFilters ? 'true' : null"
               aria-label="Shop filters">
            <div class="mb-6 flex items-center justify-between lg:hidden">
                <h2 class="font-bold text-ink">Filters</h2>
                <button x-ref="filterClose" type="button"
                        @click="mobileFilters = false; $nextTick(() => $refs.filterTrigger.focus())"
                        class="rounded-full p-2 text-ink transition hover:bg-ink/5" aria-label="Close filters">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Category --}}
            <div class="border-b border-ink/10 pb-6" x-data="{ catOpen: true }">
                <button type="button" @click="catOpen = !catOpen" class="flex w-full items-center justify-between py-1" :aria-expanded="catOpen ? 'true' : 'false'">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Category</h2>
                    <svg class="h-4 w-4 text-muted transition-transform duration-200" :class="catOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="catOpen" class="mt-3 space-y-2.5" x-data="{ categorySearch: '' }">
                    <label class="block">
                        <span class="sr-only">Search categories</span>
                        <input type="search" x-model="categorySearch" placeholder="Search categories"
                               class="h-10 w-full rounded-lg border border-ink/15 bg-white px-3 text-sm text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                    </label>
                    <button type="button" wire:click="setCategory(null)"
                            x-show="categorySearch === '' || 'all categories'.includes(categorySearch.toLowerCase())"
                            class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-sm transition hover:bg-ink/5 {{ $category === null ? 'font-bold text-ink' : 'text-muted' }}">
                        All categories
                        <span class="text-xs text-muted/70">{{ $products->total() }}</span>
                    </button>
                    @foreach($categories as $parent)
                        @php
                            $categorySearchText = strtolower($parent['name'].' '.collect($parent['children'])->pluck('name')->implode(' '));
                        @endphp
                        <div x-show="categorySearch === '' || @js($categorySearchText).includes(categorySearch.toLowerCase())"
                             class="rounded-lg {{ $category !== null && str_starts_with($category, $parent['slug']) ? 'bg-brand/5' : '' }}">
                            <button type="button" wire:click="setCategory('{{ $parent['slug'] }}')"
                                    class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-sm transition hover:bg-ink/5 {{ $category === $parent['slug'] ? 'font-bold text-brand' : 'text-ink' }}">
                                {{ $parent['name'] }}
                                @if($category === $parent['slug'])
                                    <svg class="h-4 w-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                            @if(count($parent['children']) > 0)
                                <div class="ml-3 space-y-1 border-l border-ink/10 pl-3">
                                    @foreach($parent['children'] as $child)
                                        <button type="button" wire:click="setCategory('{{ $child['slug'] }}')"
                                                class="flex w-full items-center justify-between rounded-lg px-2.5 py-1 text-left text-[13px] transition hover:bg-ink/5 {{ $category === $child['slug'] ? 'font-bold text-brand' : 'text-muted' }}">
                                            {{ $child['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Brand --}}
            <div class="border-b border-ink/10 py-6" x-data="{ brandOpen: true }">
                <button type="button" @click="brandOpen = !brandOpen" class="flex w-full items-center justify-between py-1" :aria-expanded="brandOpen ? 'true' : 'false'">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Brand</h2>
                    <svg class="h-4 w-4 text-muted transition-transform duration-200" :class="brandOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="brandOpen" class="mt-3" x-data="{ brandSearch: '' }">
                    <label class="mb-3 block">
                        <span class="sr-only">Search brands</span>
                        <input type="search" x-model="brandSearch" placeholder="Search brands"
                               class="h-10 w-full rounded-lg border border-ink/15 bg-white px-3 text-sm text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                    </label>
                    <div class="max-h-72 space-y-1.5 overflow-y-auto pr-1">
                    @foreach($brands as $brand)
                        <label x-show="brandSearch === '' || @js(strtolower($brand->name)).includes(brandSearch.toLowerCase())"
                               class="flex cursor-pointer items-center gap-3 rounded-lg px-2.5 py-1.5 text-sm transition hover:bg-ink/5">
                            <input type="checkbox"
                                   class="h-4 w-4 rounded border-ink/20 text-brand accent-brand focus:ring-brand/40"
                                   wire:click="toggleBrand('{{ $brand->slug }}')"
                                   @checked(in_array($brand->slug, $this->selectedBrands, true))
                                   aria-label="Filter by {{ $brand->name }}">
                            <span class="{{ in_array($brand->slug, $this->selectedBrands, true) ? 'font-semibold text-ink' : 'text-muted' }}">{{ $brand->name }}</span>
                            <span class="ml-auto text-xs text-muted/70">{{ $brand->products_count }}</span>
                        </label>
                    @endforeach
                    </div>
                </div>
            </div>

            {{-- Price --}}
            <div class="border-b border-ink/10 py-6" x-data="{ priceOpen: true }">
                <button type="button" @click="priceOpen = !priceOpen" class="flex w-full items-center justify-between py-1" :aria-expanded="priceOpen ? 'true' : 'false'">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Price (₹)</h2>
                    <svg class="h-4 w-4 text-muted transition-transform duration-200" :class="priceOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="priceOpen" class="mt-3">
                    <div class="flex items-center gap-3">
                        <label class="flex-1">
                            <span class="sr-only">Minimum price</span>
                            <input type="number" min="0" step="100" placeholder="Min"
                                   wire:model.live.debounce.600ms="minPrice"
                                   class="h-10 w-full rounded-lg border border-ink/15 bg-white px-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30">
                        </label>
                        <span class="text-muted/50" aria-hidden="true">—</span>
                        <label class="flex-1">
                            <span class="sr-only">Maximum price</span>
                            <input type="number" min="0" step="100" placeholder="Max"
                                   wire:model.live.debounce.600ms="maxPrice"
                                   class="h-10 w-full rounded-lg border border-ink/15 bg-white px-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30">
                        </label>
                    </div>
                </div>
            </div>

            {{-- Rating --}}
            <div class="border-b border-ink/10 py-6" x-data="{ ratingOpen: true }">
                <button type="button" @click="ratingOpen = !ratingOpen" class="flex w-full items-center justify-between py-1" :aria-expanded="ratingOpen ? 'true' : 'false'">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Customer rating</h2>
                    <svg class="h-4 w-4 text-muted transition-transform duration-200" :class="ratingOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="ratingOpen" class="mt-3 space-y-1">
                    @foreach([4, 3, 2, 1] as $rating)
                        <button type="button" wire:click="setMinRating({{ $rating }})"
                                class="flex min-h-10 w-full items-center gap-2 rounded-lg px-2.5 text-sm transition hover:bg-ink/5 {{ $minRating === $rating ? 'font-bold text-brand' : 'text-muted' }}"
                                aria-pressed="{{ $minRating === $rating ? 'true' : 'false' }}">
                            <span class="text-brand" aria-hidden="true">{{ str_repeat('★', $rating).str_repeat('☆', 5 - $rating) }}</span>
                            <span>{{ $rating }} &amp; up</span>
                        </button>
                    @endforeach
                    @if($minRating !== null)
                        <button type="button" wire:click="setMinRating(null)" class="min-h-10 px-2.5 text-xs font-bold text-brand underline underline-offset-4">Any rating</button>
                    @endif
                </div>
            </div>

            {{-- Category-aware normalized attributes --}}
            @foreach($attributeFacets as $attribute)
                <div class="border-b border-ink/10 py-6" x-data="{ attributeOpen: true }">
                    <button type="button" @click="attributeOpen = !attributeOpen" class="flex w-full items-center justify-between py-1" :aria-expanded="attributeOpen ? 'true' : 'false'">
                        <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">{{ $attribute->name }}</h2>
                        <svg class="h-4 w-4 text-muted transition-transform duration-200" :class="attributeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-cloak x-show="attributeOpen" class="mt-3 space-y-1.5">
                        @foreach($attribute->values as $value)
                            @php
                                $attributeSelected = in_array($value->slug, $attributeSelections[$attribute->slug] ?? [], true);
                            @endphp
                            <label class="flex min-h-10 cursor-pointer items-center gap-3 rounded-lg px-2.5 text-sm transition hover:bg-ink/5">
                                <input type="checkbox" wire:click="toggleAttribute('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                       class="h-4 w-4 rounded border-ink/20 text-brand accent-brand focus:ring-brand/40"
                                       @checked($attributeSelected)>
                                @if($value->color_hex)
                                    <span class="h-4 w-4 rounded-full border border-ink/15" style="background-color: {{ $value->color_hex }}" aria-hidden="true"></span>
                                @endif
                                <span class="{{ $attributeSelected ? 'font-semibold text-ink' : 'text-muted' }}">{{ $value->value }}@if($attribute->unit) {{ $attribute->unit }}@endif</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Availability --}}
            <div class="border-b border-ink/10 py-6">
                <button type="button" wire:click="toggleInStock"
                        class="flex w-full items-center justify-between py-1"
                        :aria-pressed="{{ $inStockOnly ? 'true' : 'false' }}">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Availability</h2>
                    <span class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $inStockOnly ? 'bg-brand' : 'bg-ink/15' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition {{ $inStockOnly ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </span>
                </button>
                <p class="mt-2 text-xs text-muted {{ $inStockOnly ? '' : 'opacity-60' }}">In stock only</p>
            </div>

            {{-- Deals --}}
            <div class="border-b border-ink/10 py-6">
                <button type="button" wire:click="toggleOnSale"
                        class="flex w-full items-center justify-between py-1"
                        :aria-pressed="{{ $onSale ? 'true' : 'false' }}">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Offers</h2>
                    <span class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $onSale ? 'bg-brand' : 'bg-ink/15' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition {{ $onSale ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </span>
                </button>
                <p class="mt-2 text-xs text-muted {{ $onSale ? '' : 'opacity-60' }}">On sale only (Deals)</p>
            </div>

            {{-- Clear all --}}
            @if($activeFilterCount > 0)
                <button type="button" wire:click="clearFilters"
                        class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full border border-ink/15 px-5 py-2.5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Clear all filters
                </button>
            @endif
        </aside>
        @endif

        {{-- ===== RESULTS ===== --}}
        <section aria-label="Products">
            @if($catalogHasProducts)
            {{-- Mobile toolbar --}}
            <div class="mb-6 flex items-center gap-3 lg:hidden">
                <button x-ref="filterTrigger" type="button"
                        @click="mobileFilters = true; $nextTick(() => $refs.filterClose.focus())"
                        :aria-expanded="mobileFilters" aria-controls="shop-filter-panel"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full border border-ink/15 px-5 py-2.5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m-7-7h2m10 0h2M5 12a7 7 0 1114 0 7 7 0 01-14 0z" /></svg>
                    Filters
                    @if($activeFilterCount > 0)
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand text-[10px] font-bold text-white">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                <label class="ml-auto">
                    <span class="sr-only">Sort products</span>
                    <select wire:model.live="sort" class="h-10 rounded-full border border-ink/15 bg-white px-4 text-sm font-semibold text-ink outline-none focus:border-brand">
                        <option value="featured">Featured</option>
                        <option value="newest">Newest</option>
                        <option value="price-asc">Price: Low → High</option>
                        <option value="price-desc">Price: High → Low</option>
                        <option value="discount">Biggest discount</option>
                    </select>
                </label>
            </div>

            {{-- Sort bar (desktop) + result count --}}
            <div class="mb-6 hidden items-center justify-between gap-4 lg:flex">
                <p class="text-sm text-muted" role="status" aria-live="polite" aria-atomic="true">
                    <span class="font-bold text-ink">{{ $products->total() }}</span> instruments
                    @if($this->category !== null)
                        <span class="text-muted">in selected category</span>
                    @endif
                </p>
                <div class="flex items-center gap-1.5" role="group" aria-label="Sort products">
                    @foreach([
                        'featured' => 'Featured',
                        'newest' => 'Newest',
                        'price-asc' => 'Price ↑',
                        'price-desc' => 'Price ↓',
                        'discount' => 'Discount',
                    ] as $value => $label)
                        <button type="button" wire:click="setSort('{{ $value }}')"
                                class="rounded-full px-4 py-2 text-xs font-semibold transition
                                {{ $sort === $value ? 'bg-brand text-white shadow-sm' : 'bg-white text-muted ring-1 ring-ink/10 hover:text-ink' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="sr-only lg:hidden" role="status" aria-live="polite" aria-atomic="true">{{ $products->total() }} instruments found</p>
            @endif

            {{-- Active filter chips --}}
            @if($activeFilterCount > 0)
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    @if($this->category !== null)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            {{ collect($categories)->firstWhere('slug', $this->category)['name'] ?? \Illuminate\Support\Str::headline($this->category) }}
                            <button type="button" wire:click="setCategory(null)" class="transition hover:text-brand-dark" aria-label="Remove category filter">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif
                    @foreach($this->selectedBrands as $slug)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            {{ $brands->firstWhere('slug', $slug)->name ?? \Illuminate\Support\Str::headline($slug) }}
                            <button type="button" wire:click="toggleBrand('{{ $slug }}')" class="transition hover:text-brand-dark" aria-label="Remove brand filter">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endforeach
                    @if($this->minPrice !== null || $this->maxPrice !== null)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            ₹{{ number_format((int) ($this->minPrice ?? 0)) }} – ₹{{ number_format((int) ($this->maxPrice ?? 999999)) }}
                            <button type="button" wire:click="$set('minPrice', null); $set('maxPrice', null); $wire.resetPage()" class="transition hover:text-brand-dark" aria-label="Remove price filter">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif
                    @if($inStockOnly)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            In stock
                            <button type="button" wire:click="toggleInStock" class="transition hover:text-brand-dark" aria-label="Remove stock filter">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif
                    @if($minRating !== null)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            {{ $minRating }}+ stars
                            <button type="button" wire:click="setMinRating(null)" class="transition hover:text-brand-dark" aria-label="Remove rating filter">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif
                    @foreach($attributeSelections as $attributeSlug => $valueSlugs)
                        @foreach($valueSlugs as $valueSlug)
                            @php
                                $facet = $attributeFacets->firstWhere('slug', $attributeSlug);
                                $facetValue = $facet?->values?->firstWhere('slug', $valueSlug);
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                                {{ $facet?->name ?? Str::headline($attributeSlug) }}: {{ $facetValue?->value ?? Str::headline($valueSlug) }}
                                <button type="button" wire:click="toggleAttribute('{{ $attributeSlug }}', '{{ $valueSlug }}')" class="transition hover:text-brand-dark" aria-label="Remove {{ $valueSlug }} filter">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        @endforeach
                    @endforeach
                    @if(trim((string) $this->search) !== '')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-3 py-1.5 text-xs font-semibold text-brand">
                            “{{ $this->search }}”
                            <button type="button" wire:click="$set('search', null); $wire.resetPage()" class="transition hover:text-brand-dark" aria-label="Remove search">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif
                </div>
            @endif

            {{-- Skeleton while filtering --}}
            <div wire:loading.delay.shortest aria-hidden="true">
                <div class="shop-product-grid">
                    @for($i = 0; $i < 6; $i++)
                        <div class="rounded-2xl border border-ink/10 bg-white p-4">
                            <div class="aspect-square animate-pulse rounded-xl bg-paper-dark"></div>
                            <div class="mt-4 h-3 w-1/3 animate-pulse rounded bg-paper-dark"></div>
                            <div class="mt-2 h-4 w-3/4 animate-pulse rounded bg-paper-dark"></div>
                            <div class="mt-4 h-5 w-1/2 animate-pulse rounded bg-paper-dark"></div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Product grid --}}
            <div wire:loading.remove.delay.shortest>
                @if($products->isEmpty())
                    <div class="rounded-3xl border border-dashed border-ink/15 bg-white px-6 py-20 text-center sm:py-24">
                        <p class="text-5xl" aria-hidden="true">🎸</p>
                        @if($catalogHasProducts)
                            <h2 class="mt-6 font-playfair text-2xl text-ink">Nothing matches those filters</h2>
                            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted">
                                Try removing a filter or search term to see more instruments.
                            </p>
                            <button type="button" wire:click="clearFilters"
                                    class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                                Clear all filters
                            </button>
                        @else
                            <h2 class="mt-6 font-playfair text-2xl text-ink">The catalogue is being prepared</h2>
                            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted">
                                Product information will appear here after the approved catalogue is added.
                            </p>
                            <a href="{{ route('home') }}" class="mt-8 inline-flex min-h-11 items-center rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                                Return to homepage
                            </a>
                        @endif
                    </div>
                @else
                    <div class="shop-product-grid">
                        @foreach($products as $product)
                            <x-shop-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
