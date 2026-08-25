@extends('layouts.app')

@section('title', 'Your Cart — Rythme Music Store')
@section('meta_description', 'Review selected instruments and current cart prices before checkout at Rhythm Exports.')

@section('content')
    <div class="bg-paper">
        <livewire:cart-page />
    </div>
@endsection
