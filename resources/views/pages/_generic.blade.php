@php
    $title = $page->title;
    $content = $page->content;
@endphp

<div class="bg-paper">
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-12">
        <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
            <span aria-hidden="true" class="text-ink/30">/</span>
            <span class="font-semibold text-ink" aria-current="page">{{ $title }}</span>
        </nav>

        <p class="section-kicker mb-4">Rythme</p>
        <h1 class="section-title">{{ $title }}</h1>

        @if($content)
            <div class="prose max-w-3xl text-base leading-7 text-ink/80 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_h2]:mt-8 [&_h2]:font-playfair [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-ink [&_a]:text-brand [&_a]:underline">
                {!! $content !!}
            </div>
        @endif

        <div class="mt-12">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3.5 text-sm font-bold text-white transition hover:bg-brand-dark">
                Browse the shop <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</div>
