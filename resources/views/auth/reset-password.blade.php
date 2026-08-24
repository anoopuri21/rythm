@extends('layouts.app')

@section('title', 'Set New Password — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-5 py-16 sm:px-8">
            <p class="section-kicker mb-4">Almost there</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Choose a new password</h1>

            <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                    @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">New password</span>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="Min 8 chars, letters + numbers">
                    @error('password') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Confirm new password</span>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                </label>

                <button type="submit" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Reset password
                </button>
            </form>
        </div>
    </div>
@endsection
