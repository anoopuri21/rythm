@extends('layouts.app')

@section('title', 'Your Cart — Rythme Music Store')
@section('meta_description', 'Review your cart at Rythme Music Store. Free shipping all over India, 1-year warranty and secure payments.')

@section('content')
    <div class="bg-paper">
        <livewire:cart-page />
    </div>
@endsection
