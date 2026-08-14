@extends('layouts.app')

@section('title', $page->title.' — Rythme Music Store')

@section('content')
    @include(match ($page->template) {
        'about' => 'pages._about',
        'contact' => 'pages._contact',
        default => 'pages._generic',
    }, ['page' => $page])
@endsection
