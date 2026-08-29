@extends('layouts.app')

@section('title', 'Complete payment — '.$order->order_number)

@section('content')
    <main class="mx-auto max-w-xl px-5 py-16 text-center sm:px-8" x-data="retryPayment()" x-init="open()">
        <p class="section-kicker mb-4">Secure payment</p>
        <h1 class="section-title">Complete payment</h1>
        <p class="mt-4 text-sm leading-6 text-muted">
            Order {{ $order->order_number }} · ₹{{ number_format((float) $order->total, 2) }}
        </p>
        <p class="mt-6 rounded-xl bg-brand/5 px-4 py-3 text-sm text-ink" role="status" x-text="message">
            Opening the payment provider…
        </p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <button type="button" @click="open()" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white hover:bg-brand-dark">
                Open payment window
            </button>
            <a href="{{ route('orders.show', $order) }}" class="rounded-full border border-ink/15 px-6 py-3 text-sm font-semibold text-ink">
                Return to order
            </a>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <script>
        function retryPayment() {
            return {
                message: 'Opening the payment provider…',
                open() {
                    if (typeof window.Razorpay === 'undefined') {
                        this.message = 'The payment window is still loading. Please wait, then use the button below.';
                        return;
                    }

                    new window.Razorpay({{ Illuminate\Support\Js::from($options) }}).open();
                    this.message = 'Payment window opened. Complete payment there, or return to your order.';
                }
            };
        }
    </script>
@endpush
