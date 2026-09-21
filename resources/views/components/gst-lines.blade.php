@props([
    'cgst' => 0,
    'sgst' => 0,
    'igst' => 0,
    'tax' => 0,
    'enabled' => false,
])

@php
    $cgst = (float) $cgst;
    $sgst = (float) $sgst;
    $igst = (float) $igst;
    $tax = (float) $tax;
@endphp

@if($enabled && $tax > 0)
    @if($igst > 0)
        <div {{ $attributes->class('flex items-center justify-between') }}>
            <dt class="text-ink/70">IGST</dt>
            <dd class="font-semibold text-ink">₹{{ number_format($igst, 2) }}</dd>
        </div>
    @else
        @if($cgst > 0)
            <div {{ $attributes->class('flex items-center justify-between') }}>
                <dt class="text-ink/70">CGST</dt>
                <dd class="font-semibold text-ink">₹{{ number_format($cgst, 2) }}</dd>
            </div>
        @endif
        @if($sgst > 0)
            <div class="flex items-center justify-between">
                <dt class="text-ink/70">SGST</dt>
                <dd class="font-semibold text-ink">₹{{ number_format($sgst, 2) }}</dd>
            </div>
        @endif
    @endif
@endif
