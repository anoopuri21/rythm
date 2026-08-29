@extends('layouts.app')

@section('title', 'Secure Checkout — Rythme Music Store')
@section('meta_description', 'Review your address and server-calculated order totals before completing checkout at Rhythm Exports.')

@section('content')
    <div class="bg-paper">
        <livewire:checkout-wizard />
    </div>
@endsection
