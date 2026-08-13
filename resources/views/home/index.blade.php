@extends('layouts.app')

@section('title', 'Rhythm Exports - Feel The Music, Own The Sound')
@section('meta_description', 'Shop premium musical instruments at Rhythm Exports. Guitars, Keyboards, Drums, Pro Audio and more from top brands like Fender, Yamaha, Gibson. Free shipping all over India.')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero-guitar.jpg') }}" fetchpriority="high">
@endpush

@section('content')
    @include('home._hero', ['heroMode' => $heroMode])
    @include('home._categories', ['homeSections' => $homeSections])
    @include('home._bestsellers', ['homeSections' => $homeSections])
    @include('home._why-rythme', ['homeSections' => $homeSections])
    @include('home._brands', ['homeSections' => $homeSections])
    @include('home._numbers', ['homeSections' => $homeSections])
    @include('home._new-arrivals', ['homeSections' => $homeSections])
    @include('home._deals', ['homeSections' => $homeSections])
    @include('home._video-showcase', ['homeSections' => $homeSections])
    @include('home._stories', ['homeSections' => $homeSections])
    @include('home._testimonials', ['homeSections' => $homeSections])
    @include('home._comparison', ['homeSections' => $homeSections])
    @include('home._ugc', ['homeSections' => $homeSections])
    @include('home._faq', ['homeSections' => $homeSections])
    @include('home._footer', ['homeSections' => $homeSections])
@endsection
