@extends('layouts.app')

@section('title', 'Secure Checkout — Rythme Music Store')
@section('meta_description', 'Review your address and server-calculated order totals before completing checkout at Rhythm Exports.')
@push('head')
    {{-- Load Razorpay before the Livewire payment step is rendered or clicked. --}}
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
@endpush

@section('content')
    <div class="bg-paper">
        <livewire:checkout-wizard />
    </div>
@endsection
