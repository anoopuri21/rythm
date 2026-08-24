@extends('layouts.app')

@section('title', 'Sign In — Rythme Music Store')
@section('meta_description', 'Sign in to your Rythme Music Store account to view orders, manage your wishlist and check out faster.')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-5 py-16 sm:px-8">
            <p class="section-kicker mb-4">Welcome back</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Sign in</h1>
            <p class="mt-4 text-sm leading-6 text-muted">
                New here? <a href="{{ route('register') }}" class="font-bold text-brand transition hover:text-brand-dark">Create an account</a>
            </p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="you@example.com">
                    @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Password</span>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="••••••••">
                    @error('password') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 text-sm text-ink">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-ink/20 text-brand accent-brand focus:ring-brand/40">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand transition hover:text-brand-dark">Forgot password?</a>
                </div>

                <button type="submit" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-muted">
                Your cart stays with you — sign in and it will be waiting.
            </p>
        </div>
    </div>
@endsection
