<section class="mt-16 scroll-mt-28" aria-labelledby="product-questions-title">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-kicker mb-3">Product help</p>
            <h2 id="product-questions-title" class="font-playfair text-2xl font-bold text-ink sm:text-3xl">Questions &amp; answers</h2>
        </div>
        @if($questions->total() > 0)
            <p class="text-sm text-muted">{{ $questions->total() }} answered {{ Str::plural('question', $questions->total()) }}</p>
        @endif
    </div>

    <form wire:submit="submit" class="mt-8 max-w-2xl rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
        <h3 class="text-sm font-bold text-ink">Ask about this product</h3>
        <p class="mt-2 text-xs leading-5 text-muted">Questions are reviewed before publication. Answers come from store staff.</p>

        @if($submitted)
            <p class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">
                Thanks! Your question is awaiting moderation.
            </p>
        @endif

        @if($error)
            <p class="mt-4 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ $error }}</p>
        @endif

        <label for="product-question-{{ $product->id }}" class="mt-5 block text-xs font-bold uppercase tracking-wide text-muted">Your question</label>
        <textarea id="product-question-{{ $product->id }}" wire:model="question" rows="3" minlength="10" maxlength="1000" required
                  aria-describedby="product-question-help-{{ $product->id }}"
                  class="mt-2 w-full rounded-xl border border-ink/15 bg-paper px-4 py-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"></textarea>
        <p id="product-question-help-{{ $product->id }}" class="mt-2 text-[11px] text-muted">Do not include payment details, phone numbers or other sensitive information.</p>
        @error('question') <p class="mt-2 text-xs font-semibold text-brand" role="alert">{{ $message }}</p> @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                class="mt-4 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark disabled:opacity-60">
            <span wire:loading.remove wire:target="submit">Submit question</span>
            <span wire:loading wire:target="submit">Submitting…</span>
        </button>
    </form>

    <div class="mt-8 space-y-4">
        @forelse($questions as $item)
            <article class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-8" wire:key="question-{{ $item->id }}">
                <div class="flex gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-sm font-bold text-brand" aria-hidden="true">Q</span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold leading-6 text-ink">{{ $item->question }}</h3>
                        <p class="mt-1 text-[11px] text-muted">Asked by a customer · {{ $item->created_at?->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="mt-5 flex gap-3 border-t border-ink/10 pt-5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-ink text-sm font-bold text-white" aria-hidden="true">A</span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-muted">Rhythm Exports</p>
                        <p class="mt-2 text-sm leading-6 text-ink/80">{{ $item->answer }}</p>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-ink/15 bg-white px-6 py-10 text-center text-sm text-muted">
                No answered questions yet. Ask the first product question above.
            </p>
        @endforelse
    </div>

    @if($questions->hasPages())
        <div class="mt-8">{{ $questions->links() }}</div>
    @endif
</section>
