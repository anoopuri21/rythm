@extends('layouts.app')

@section('title', 'Create Account — Rythme Music Store')
@section('meta_description', 'Create your Rythme Music Store account for faster checkout, order tracking and a personal wishlist.')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-5 py-16 sm:px-8">
            <p class="section-kicker mb-4">Join the family</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Create account</h1>
            <p class="mt-4 text-sm leading-6 text-muted">
                Already have one? <a href="{{ route('login') }}" class="font-bold text-brand transition hover:text-brand-dark">Sign in</a>
            </p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
                @csrf

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Full name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="Anoop Puri">
                    @error('name') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                           placeholder="you@example.com">
                    @error('email') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                </label>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Password</span>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="Min 8 chars">
                        @error('password') <span class="mt-1.5 block text-xs font-semibold text-brand">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Confirm</span>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="h-12 w-full rounded-xl border border-ink/15 bg-white px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                               placeholder="Repeat password">
                    </label>
                </div>

                <button type="submit" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                    Create account
                </button>
            </form>
        </div>
    </div>
@endsection
