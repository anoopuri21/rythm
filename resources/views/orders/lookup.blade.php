@extends('layouts.app')

@section('title', 'Track Your Order — Rhythm Exports')
@section('meta_description', 'Track your Rhythm Exports order with your order number and email address.')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[60vh] max-w-md flex-col justify-center px-5 py-16 sm:px-8">
            <p class="section-kicker mb-4">Where is my order?</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Track your order</h1>
            <p class="mt-4 text-sm leading-6 text-muted">
                Enter your order number and the email you used at checkout.
            </p>

            <form method="POST" action="{{ route('orders.lookup.post') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Order number</span>
                    <input type="text" name="order_number" value="{{ old('order_number') }}" required
                           placeholder="RYM-2026-XXXXXX"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 font-mono text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                    @error('order_number') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                    @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Track order
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-muted">
                Signed in? <a href="{{ route('account.index') }}" class="font-bold text-brand">View all your orders</a>
            </p>
        </div>
    </div>
@endsection
