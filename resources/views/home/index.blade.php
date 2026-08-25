@extends('layouts.app')

@section('title', 'Rhythm Exports - Feel The Music, Own The Sound')
@section('meta_description', 'Shop premium musical instruments at Rhythm Exports. Guitars, Keyboards, Drums, Pro Audio and more from top brands like Fender, Yamaha, Gibson. Free shipping all over India.')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero/grid-slide-guitar.jpg') }}" fetchpriority="high">
@endpush

@section('content')
    @include('home._hero', ['heroMode' => $heroMode, 'homepage' => $homepage])
    @include('home._categories', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._bestsellers', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._why-rythme', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._brands', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._numbers', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._new-arrivals', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._deals', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._video-showcase', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._stories', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._testimonials', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._comparison', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._ugc', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._faq', ['homeSections' => $homeSections, 'homepage' => $homepage])
@endsection
