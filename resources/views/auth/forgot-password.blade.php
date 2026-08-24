@extends('layouts.app')

@section('title', 'Reset Password — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-5 py-16 sm:px-8">
            <p class="section-kicker mb-4">No worries</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Reset password</h1>
            <p class="mt-4 text-sm leading-6 text-muted">
                Enter your email and we will send you a secure reset link.
            </p>

            @if(session('status'))
                <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="you@example.com">
                    @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <button type="submit" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Send reset link
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-muted">
                Remembered it? <a href="{{ route('login') }}" class="font-bold text-brand">Back to sign in</a>
            </p>
        </div>
    </div>
@endsection
