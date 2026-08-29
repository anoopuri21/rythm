@extends('layouts.app')

@section('title', 'Request a return — '.$order->order_number.' — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 sm:py-14">
            <a href="{{ route('orders.show', $order) }}" class="text-link text-sm">← Back to order</a>
            <div class="mt-6 rounded-3xl border border-ink/10 bg-white p-6 sm:p-10">
                <p class="section-kicker mb-3">Order {{ $order->order_number }}</p>
                <h1 class="section-title">Request a return</h1>
                <p class="mt-3 text-sm leading-6 text-muted">Choose only the items and quantities you want reviewed. Submitting a request does not approve a return or initiate a refund.</p>

                @if($errors->any())
                    <div class="mt-6 rounded-xl bg-brand/10 px-4 py-3 text-sm text-brand" role="alert">
                        <p class="font-bold">Please review your request.</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('returns.store', $order) }}" class="mt-8 space-y-7">
                    @csrf
                    <input type="hidden" name="request_token" value="{{ old('request_token', $requestToken) }}">

                    <fieldset>
                        <legend class="text-sm font-bold text-ink">Items for review</legend>
                        <div class="mt-3 divide-y divide-ink/10 rounded-2xl border border-ink/10">
                            @foreach($order->items as $item)
                                <label class="flex items-center justify-between gap-5 p-4">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-ink">{{ $item->name }}</span>
                                        <span class="block text-xs text-muted">{{ $item->sku }} · Ordered quantity {{ $item->qty }}</span>
                                    </span>
                                    <input type="number" name="items[{{ $item->id }}]" value="{{ old('items.'.$item->id, 0) }}" min="0" max="{{ $item->qty }}" inputmode="numeric" aria-label="Return quantity for {{ $item->name }}" class="w-20 rounded-xl border border-ink/15 px-3 py-2 text-sm">
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div>
                        <label for="return_reason_id" class="text-sm font-bold text-ink">Reason</label>
                        <select id="return_reason_id" name="return_reason_id" required class="mt-2 w-full rounded-xl border border-ink/15 bg-white px-4 py-3 text-sm">
                            <option value="">Choose an approved reason</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason->id }}" @selected((string) old('return_reason_id') === (string) $reason->id)>{{ $reason->name }}</option>
                            @endforeach
                        </select>
                        @foreach($reasons as $reason)
                            @if($reason->customer_guidance)<p class="mt-2 text-xs text-muted"><span class="font-semibold">{{ $reason->name }}:</span> {{ $reason->customer_guidance }}</p>@endif
                        @endforeach
                    </div>

                    <div>
                        <label for="customer_note" class="text-sm font-bold text-ink">Additional details <span class="font-normal text-muted">(optional)</span></label>
                        <textarea id="customer_note" name="customer_note" maxlength="2000" rows="5" class="mt-2 w-full rounded-xl border border-ink/15 px-4 py-3 text-sm" placeholder="Describe the item condition or issue without including payment details.">{{ old('customer_note') }}</textarea>
                    </div>

                    <div class="rounded-xl bg-paper px-4 py-3 text-xs leading-5 text-muted">
                        This request records a logistical review only. Approval, receipt, and any payment-provider refund are separate outcomes.
                    </div>

                    <button type="submit" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Submit for review</button>
                </form>
            </div>
        </div>
    </div>
@endsection
