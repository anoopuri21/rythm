@extends('layouts.app')

@section('title', 'Close Your Account — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-lg flex-col justify-center px-5 py-16 sm:px-8">
            @if($state === 'mismatch')
                <p class="section-kicker mb-4">Wrong account</p>
                <h1 class="font-playfair text-4xl font-bold text-ink">This link is for a different account</h1>
                <p class="mt-4 text-sm leading-6 text-muted">Sign in with the account that requested the email, then open the link again. You are still signed in to this account.</p>
            @elseif($state === 'invalid')
                <p class="section-kicker mb-4">Link expired</p>
                <h1 class="font-playfair text-4xl font-bold text-ink">This closure link is no longer valid</h1>
                <p class="mt-4 text-sm leading-6 text-muted">Request a new link from account settings. Your account has not been changed.</p>
                <a href="{{ route('account.index') }}" class="mt-8 inline-flex w-fit rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Back to account</a>
            @else
                <p class="section-kicker mb-4">Close account</p>
                <h1 class="font-playfair text-4xl font-bold text-ink">Deactivate or remove</h1>
                <p class="mt-4 text-sm leading-6 text-muted">This page only applies to the account you are signed in with. Enter your current password to confirm.</p>
                @error('current_password')
                    <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">{{ $message }}</p>
                @enderror

                <form method="POST" action="{{ $deactivateUrl }}" class="mt-8 space-y-4 rounded-3xl border border-ink/10 bg-white p-6">
                    @csrf
                    <h2 class="text-sm font-bold text-ink">Deactivate</h2>
                    <p class="text-sm leading-6 text-muted">Your profile and orders stay on file, and this login stops working.</p>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Current password</span>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                    </label>
                    <button type="submit" class="rounded-full bg-ink px-7 py-3 text-sm font-bold text-white transition hover:bg-brand">Deactivate account</button>
                </form>

                <form method="POST" action="{{ $removeUrl }}" class="mt-6 space-y-4 rounded-3xl border border-brand/20 bg-white p-6">
                    @csrf
                    <h2 class="text-sm font-bold text-ink">Remove personal data</h2>
                    <p class="text-sm leading-6 text-muted">This cannot be undone. Your name, email, password, addresses, cart, wishlist, and stock alerts are cleared. Orders and reviews stay for invoices, under the name Deleted customer.</p>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Current password</span>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                    </label>
                    <button type="submit" class="rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Remove personal data</button>
                </form>
            @endif
        </div>
    </div>
@endsection
