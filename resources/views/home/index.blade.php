@extends('layouts.app')

@section('title', 'Rythme Music Store - Feel The Music, Own The Sound')
@section('meta_description', 'Shop premium musical instruments at Rythme Music Store. Guitars, Keyboards, Drums, Pro Audio and more from top brands like Fender, Yamaha, Gibson. Free shipping all over India.')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero-guitar.jpg') }}" fetchpriority="high">
@endpush

@section('content')
    @include('home._hero', ['heroMode' => $heroMode])
    @include('home._categories')
    @include('home._bestsellers')
    @include('home._why-rythme')
    @include('home._brands')
    @include('home._numbers')
    @include('home._new-arrivals')
    @include('home._deals')
    @include('home._video-showcase')
    @include('home._stories')
    @include('home._testimonials')
    @include('home._comparison')
    @include('home._ugc')
    @include('home._faq')
    @include('home._footer')
@endsection
