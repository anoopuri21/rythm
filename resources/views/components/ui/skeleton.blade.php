@props(['label' => 'Loading content'])

<div role="status" aria-label="{{ $label }}" {{ $attributes->class(['ui-skeleton']) }}>
    <span class="sr-only">{{ $label }}</span>
</div>
