@extends('layouts.app')

@section('title', 'Your Wishlist — Rythme Music Store')
@section('meta_description', 'Your saved instruments at Rythme Music Store. Move them to your cart whenever you are ready.')

@section('content')
    <div class="bg-paper">
        <livewire:wishlist-page />
    </div>
@endsection
