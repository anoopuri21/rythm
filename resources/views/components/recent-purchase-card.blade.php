@php
    // Front-end-only design preview. These are synthetic cards and must remain
    // visibly labelled as demo content until a consented data source exists.
    $demoPurchases = [
        [
            'product' => 'Fender Player Stratocaster',
            'unit_price' => '₹72,999',
            'user' => 'Aarav M.',
            'detail' => 'A versatile electric guitar for warm cleans and expressive leads.',
        ],
        [
            'product' => 'Roland FP-30X Digital Piano',
            'unit_price' => '₹68,490',
            'user' => 'Meera S.',
            'detail' => 'Weighted keys and rich piano tones for practice, studio and stage.',
        ],
        [
            'product' => 'Shure SM58 Vocal Microphone',
            'unit_price' => '₹9,499',
            'user' => 'Kabir R.',
            'detail' => 'A dependable dynamic mic ready for vocals, rehearsals and live sets.',
        ],
        [
            'product' => 'Yamaha DTX402K Electronic Drum Kit',
            'unit_price' => '₹49,990',
            'user' => 'Nisha P.',
            'detail' => 'Quiet practice control with responsive pads and guided rhythms.',
        ],
        [
            'product' => 'Audio-Technica ATH-M20x',
            'unit_price' => '₹5,999',
            'user' => 'Rohan K.',
            'detail' => 'Clear, focused monitoring for everyday recording and listening.',
        ],
    ];
@endphp

<div id="recent-purchase-preview" class="recent-purchase" data-recent-purchase-demo>
    <div class="recent-purchase__head">
        <div>
            <p class="recent-purchase__demo">Recent buy</p>
        </div>
        <button type="button" class="recent-purchase__close" data-recent-purchase-close aria-label="Close recent purchase preview" title="Close preview">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div class="recent-purchase__cards" aria-live="polite">
        @foreach($demoPurchases as $purchase)
            <article class="recent-purchase__card{{ $loop->first ? ' is-active' : '' }}" data-recent-purchase-card aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <p class="recent-purchase__product">{{ $purchase['product'] }}</p>
                <div class="recent-purchase__meta">
                    <span class="recent-purchase__price"><span class="sr-only">Unit price: </span>{{ $purchase['unit_price'] }}</span>
                    <span class="recent-purchase__user"><span class="sr-only">Demo user: </span>{{ $purchase['user'] }}</span>
                </div>
                <p class="recent-purchase__detail">{{ $purchase['detail'] }}</p>
            </article>
        @endforeach
    </div>
</div>
