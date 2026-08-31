@extends('layouts.app')

@section('title', 'Rhythm Exports - Feel The Music, Own The Sound')
@section('meta_description', 'Explore guitars, keyboards, drums, pro audio and musical-instrument accessories from leading brands at Rhythm Exports.')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero/grid-slide-guitar.jpg') }}" fetchpriority="high">
@endpush

@section('content')
    @include('home._hero', ['heroMode' => $heroMode, 'homepage' => $homepage])
    @include('home._offer-marquee', ['homepage' => $homepage])
    @include('home._usp-strip')
    @include('home._categories', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._new-arrivals', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._trending', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._category-product-rows', ['homepage' => $homepage])
    @include('home._promo-banners')
    @include('home._advantages', ['homeSections' => $homeSections])
    @include('home._deals', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._category-banners')
    @include('home._recently-launched', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._brands', ['homeSections' => $homeSections, 'homepage' => $homepage])
    @include('home._confidence', ['homepage' => $homepage])
@endsection
