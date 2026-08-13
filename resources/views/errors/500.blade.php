@extends('layouts.app')

@section('title', 'Something Went Wrong — Rhythm Exports')

@section('content')
    <div class="bg-paper">
        <div class="mx-auto flex min-h-[60vh] max-w-lg flex-col items-center justify-center px-5 py-20 text-center sm:px-8">
            <p class="font-bebas text-[7rem] leading-none text-brand/15 sm:text-[9rem]" aria-hidden="true">500</p>
            <h1 class="mt-2 font-playfair text-3xl font-bold text-ink sm:text-4xl">A string snapped backstage</h1>
            <p class="mt-4 max-w-md text-sm leading-6 text-muted">
                Something went wrong on our side. The team has been notified — please try again in a moment.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('home') }}" class="rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Back home</a>
                <a href="{{ route('contact') }}" class="text-link text-sm">Report this</a>
            </div>
        </div>
    </div>
@endsection
