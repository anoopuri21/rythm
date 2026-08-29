@php
    $testimonials = collect($homepage['testimonials'] ?? [])->take(3);
    $faqs = collect($homepage['faqs'] ?? [])->take(6);
@endphp

@if($testimonials->isNotEmpty())
<section class="ui-section-separator bg-paper-dark" aria-labelledby="home-testimonials-title">
    <div class="mx-auto max-w-[1520px] px-5 sm:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="section-kicker mb-3 justify-center">Verified customer voices</p>
            <h2 id="home-testimonials-title" class="text-3xl font-bold text-ink sm:text-4xl">Chosen by people who care about sound</h2>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach($testimonials as $testimonial)
                <figure class="ui-card p-6 sm:p-7">
                    <blockquote class="text-sm leading-7 text-ink/75">“{{ $testimonial->content }}”</blockquote>
                    <figcaption class="mt-5 border-t border-ink/5 pt-4">
                        <p class="font-semibold text-ink">{{ $testimonial->title }}</p>
                        @if($testimonial->subtitle)<p class="mt-1 text-xs text-muted">{{ $testimonial->subtitle }}</p>@endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($faqs->isNotEmpty())
<section class="ui-section-separator" aria-labelledby="home-faq-title">
    <div class="mx-auto grid max-w-[1200px] gap-10 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr]">
        <div>
            <p class="section-kicker mb-3">Before you order</p>
            <h2 id="home-faq-title" class="text-3xl font-bold text-ink sm:text-4xl">Questions, answered clearly</h2>
            <p class="mt-4 text-sm leading-7 text-muted">Review practical information, then contact the team if your instrument or order needs individual guidance.</p>
            <a href="/faqs" class="text-link mt-6 inline-flex text-sm">View all FAQs <span aria-hidden="true">→</span></a>
        </div>
        <div class="divide-y divide-ink/10">
            @foreach($faqs as $faq)
                <details class="group py-5 first:pt-0">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold text-ink">
                        {{ $faq->question }}
                        <span class="text-xl font-normal text-brand transition group-open:rotate-45" aria-hidden="true">＋</span>
                    </summary>
                    <div class="prose-sm max-w-none pt-3 leading-7 text-muted">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="mx-auto max-w-[1520px] px-5 pb-16 sm:px-8 sm:pb-24" aria-labelledby="home-final-cta-title">
    <div class="overflow-hidden rounded-[2rem] bg-ink px-6 py-12 text-center text-white sm:px-10 sm:py-16">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-light">Find your next instrument</p>
        <h2 id="home-final-cta-title" class="mx-auto mt-3 max-w-3xl text-3xl font-bold sm:text-5xl">A focused catalogue, clear product detail and help when you need it.</h2>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-ui.button href="{{ route('shop.index') }}" size="lg">Explore the catalogue</x-ui.button>
            <x-ui.button href="{{ route('contact') }}" variant="secondary" size="lg">Ask the team</x-ui.button>
        </div>
    </div>
</section>
