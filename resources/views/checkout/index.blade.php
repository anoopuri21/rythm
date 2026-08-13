@extends('layouts.app')

@section('title', 'Secure Checkout — Rythme Music Store')
@section('meta_description', 'Complete your purchase at Rythme Music Store. Free shipping, 1-year warranty, secure payments via Razorpay.')

@section('content')
    <div class="bg-paper">
        <livewire:checkout-wizard />
    </div>
@endsection
