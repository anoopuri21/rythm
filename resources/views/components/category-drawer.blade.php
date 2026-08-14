@props(['categories' => []])

{{-- Amazon-style "Shop by Category" left drawer (drill-down, 2 levels).
     State lives in Alpine store `catDrawer` so any trigger can open it.
     Toggle/close: keyboard Esc, backdrop click, close button. --}}

{{-- Backdrop --}}
<div x-cloak x-show="$store.catDrawer.open" x-transition.opacity.duration.250ms
     class="fixed inset-0 z-[80] bg-black/55 backdrop-blur-sm"
     @click="$store.catDrawer.open = false"
     aria-hidden="true"></div>

{{-- Panel --}}
<div x-cloak
     x-show="$store.catDrawer.open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="-translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="-translate-x-full"
     @keydown.escape.window="$store.catDrawer.open = false"
     x-effect="$store.catDrawer.open ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')"
     x-data="{ level: null, breadcrumb: null }"
     x-init="$watch('$store.catDrawer.open', value => {
         if (value) { level = null; breadcrumb = null; $nextTick(() => $refs.closeBtn?.focus()); }
     })"
     role="dialog" aria-modal="true" aria-label="Shop by category"
     class="fixed inset-y-0 left-0 z-[90] flex w-[88%] max-w-sm flex-col bg-paper shadow-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-ink/10 bg-ink px-6 py-5 text-white">
        <p class="flex items-center gap-2.5 text-sm font-bold uppercase tracking-[0.18em]">
            <svg class="h-5 w-5 text-brand-light" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Shop by Category
        </p>
        <button type="button" x-ref="closeBtn" @click="$store.catDrawer.open = false"
                class="rounded-full p-2 text-white/70 transition hover:bg-white/10 hover:text-white" aria-label="Close category menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    {{-- Drill navigation --}}
    <div class="flex-1 overflow-y-auto py-4">
        {{-- Root level --}}
        <div x-show="level === null">
            <a href="/shop" @click="$store.catDrawer.open = false"
               class="flex items-center justify-between px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-brand/5">
                All Products
                <svg class="h-4 w-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
            <p class="px-6 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.24em] text-muted">Categories</p>
            <ul>
                @foreach($categories as $parent)
                    <li>
                        @if(count($parent['children']) > 0)
                            <button type="button"
                                    @click="level = '{{ $parent['slug'] }}'; breadcrumb = '{{ $parent['name'] }}'"
                                    class="flex w-full items-center justify-between px-6 py-3.5 text-left text-sm font-medium text-ink transition hover:bg-brand/5 hover:text-brand"
                                    :aria-expanded="level === '{{ $parent['slug'] }}' ? 'true' : 'false'">
                                {{ $parent['name'] }}
                                <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        @else
                            <a href="/shop?category={{ $parent['slug'] }}" @click="$store.catDrawer.open = false"
                               class="flex items-center justify-between px-6 py-3.5 text-sm font-medium text-ink transition hover:bg-brand/5 hover:text-brand">
                                {{ $parent['name'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Drill level --}}
        @foreach($categories as $parent)
            <div x-show="level === '{{ $parent['slug'] }}'" x-cloak>
                <button type="button" @click="level = null; breadcrumb = null"
                        class="flex w-full items-center gap-2.5 px-6 py-3 text-sm font-semibold text-muted transition hover:text-brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    All Categories
                </button>
                <p class="border-b border-ink/5 px-6 pb-3 pt-1 text-xs font-bold uppercase tracking-[0.2em] text-brand">{{ $parent['name'] }}</p>
                <a href="/shop?category={{ $parent['slug'] }}" @click="$store.catDrawer.open = false"
                   class="mx-4 mt-3 flex items-center justify-between rounded-xl bg-ink px-4 py-3 text-sm font-bold text-white transition hover:bg-brand">
                    View all {{ $parent['name'] }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
                <ul class="mt-2">
                    @foreach($parent['children'] as $child)
                        <li>
                            <a href="/shop?category={{ $child['slug'] }}" @click="$store.catDrawer.open = false"
                               class="flex items-center justify-between px-6 py-3 text-sm text-ink transition hover:bg-brand/5 hover:text-brand">
                                {{ $child['name'] }}
                                <svg class="h-4 w-4 text-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    {{-- Footer note --}}
    <div class="border-t border-ink/10 bg-paper-dark px-6 py-4">
        <a href="/shop" @click="$store.catDrawer.open = false" class="text-xs font-semibold text-muted transition hover:text-brand">
            Can't find it? Browse the full catalogue →
        </a>
    </div>
</div>
