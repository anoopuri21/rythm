@extends('layouts.app')

@section('title', 'Shop All Instruments — Guitars, Keyboards, Drums, Pro Audio | Rythme Music Store')
@section('meta_description', 'Browse guitars, digital pianos, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.')

@section('content')
    <div class="bg-paper">
        {{-- Page header
        <header class="border-b border-ink/10 bg-paper">
            <div class="mx-auto max-w-7xl px-5 pb-10 pt-14 sm:px-8 sm:pb-14 sm:pt-20 lg:px-12">
                <nav aria-label="Breadcrumb" class="mb-6 flex items-center gap-2 text-xs text-muted">
                    <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
                    <span aria-hidden="true" class="text-ink/30">/</span>
                    <span class="font-semibold text-ink" aria-current="page">Shop</span>
                </nav>

                <p class="section-kicker mb-4">The Rythme Collection</p>
                <h1 class="section-title">Shop instruments,<br><em>built to inspire.</em></h1>
                <p class="mt-6 max-w-2xl text-base leading-7 text-muted sm:text-lg">
                    Explore guitars, keyboards, drums, microphones and studio gear. Filter the catalogue
                    by category, brand, availability and budget to find the right instrument for your sound.
                </p>
            </div>
        </header>
         --}}
        {{-- Livewire shop grid: filters + sort + products (zero-refresh) --}}
        <livewire:shop-index />
    </div>
@endsection
