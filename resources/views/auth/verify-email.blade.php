@extends('layouts.app')

@section('title', 'Verify Your Email — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-5 py-16 text-center sm:px-8">
            <p class="text-6xl" aria-hidden="true">📬</p>
            <p class="section-kicker mb-4 mt-8 justify-center">One more step</p>
            <h1 class="font-playfair text-4xl font-bold text-ink sm:text-5xl">Verify your email</h1>
            <p class="mt-4 text-sm leading-6 text-muted">
                We sent a verification link to <span class="font-bold text-ink">{{ auth()->user()?->email }}</span>.
                Click it to activate your account and unlock everything.
            </p>

            @if(session('status'))
                <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('status') }}</p>
            @endif

            <div class="mt-8 flex flex-col items-center gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="rounded-full bg-brand px-8 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(17,17,17,0.25)] transition hover:bg-brand-dark">
                        Resend verification email
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-muted transition hover:text-brand">Sign out</button>
                </form>
            </div>
        </div>
    </div>
@endsection
