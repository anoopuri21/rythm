<div class="mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-kicker mb-3">Real voices</p>
            <h2 class="font-playfair text-2xl font-bold text-ink sm:text-3xl">Customer reviews</h2>
        </div>
        @if($summary['count'] > 0)
            <div class="flex items-center gap-3 rounded-2xl border border-ink/10 bg-white px-5 py-3">
                <span class="text-3xl font-bold text-ink">{{ $summary['avg'] }}</span>
                <div>
                    <p class="text-sm tracking-wider text-amber-400" aria-hidden="true">
                        {{ str_repeat('★', (int) round($summary['avg'])) }}{{ str_repeat('☆', 5 - (int) round($summary['avg'])) }}
                    </p>
                    <p class="text-xs text-muted">{{ $summary['count'] }} {{ Str::plural('review', $summary['count']) }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Summary bars --}}
    @if($summary['count'] > 0)
        <div class="mt-6 grid gap-2 sm:grid-cols-2 lg:max-w-xl">
            @foreach(array_reverse($summary['stars'], true) as $star => $count)
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-10 shrink-0 font-semibold text-muted">{{ $star }}★</span>
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-ink/10">
                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $summary['count'] > 0 ? round($count / $summary['count'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="w-8 shrink-0 text-right text-muted">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Submit form --}}
    <div class="mt-8 max-w-2xl rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
        <h3 class="text-sm font-bold text-ink">Write a review</h3>

        @if($submitted)
            <p class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                Thanks! Your review is awaiting moderation.
            </p>
        @elseif($error)
            <p class="mt-4 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ $error }}</p>
        @else
            <div class="mt-4 flex items-center gap-1.5" role="radiogroup" aria-label="Star rating">
                @for($star = 1; $star <= 5; $star++)
                    <button type="button" wire:click="setRating({{ $star }})"
                            class="text-2xl transition hover:scale-110 {{ $rating >= $star ? 'text-amber-400' : 'text-ink/15' }}"
                            :aria-label="'Rate {{ $star }} star{{ $star > 1 ? 's' : '' }}'">
                        ★
                    </button>
                @endfor
                <span class="ml-2 text-xs text-muted">{{ $rating }}/5</span>
            </div>
            <textarea wire:model="comment" rows="3" maxlength="2000" placeholder="How does it play? What do you love?"
                      class="mt-4 w-full rounded-xl border border-ink/15 bg-paper px-4 py-3 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"></textarea>
            <button type="button" wire:click="submit" wire:loading.attr="disabled"
                    class="mt-4 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark disabled:opacity-60">
                <span wire:loading.remove>Submit review</span>
                <span wire:loading>Submitting…</span>
            </button>
            <p class="mt-3 text-[11px] text-muted">Verified purchases only — reviews appear after moderation.</p>
        @endif
    </div>

    {{-- List --}}
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @forelse($paginated as $review)
            <article class="rounded-3xl border border-ink/10 bg-white p-6" wire:key="review-{{ $review->id }}">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-ink">{{ $review->user?->name ?? 'Verified buyer' }}</p>
                    <p class="text-sm tracking-wider text-amber-400" aria-label="{{ $review->rating }} out of 5 stars">
                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                    </p>
                </div>
                <p class="mt-1 text-[11px] text-muted">{{ $review->created_at?->format('d M Y') }}</p>
                @if($review->comment)
                    <p class="mt-3 text-sm leading-6 text-ink/80">{{ $review->comment }}</p>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-ink/15 bg-white px-6 py-10 text-center text-sm text-muted sm:col-span-2">
                No reviews yet — be the first to share your experience.
            </p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $paginated->links() }}
    </div>
</div>
