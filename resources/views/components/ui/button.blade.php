@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'block' => false,
    'disabled' => false,
])

@php
    $classes = [
        'ui-btn',
        'ui-btn--' . $variant,
        $size !== 'md' ? 'ui-btn--' . $size : null,
        $block ? 'ui-btn--block' : null,
    ];
@endphp

@if($href)
    <a href="{{ $disabled ? '#' : $href }}"
       @if($disabled) aria-disabled="true" tabindex="-1" @endif
       {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled)
            {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
