@props(['variant' => 'info', 'title' => null])

<div role="{{ $variant === 'danger' ? 'alert' : 'status' }}"
     {{ $attributes->class(['ui-alert', 'ui-alert--' . $variant]) }}>
    <div>
        @if($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div>{{ $slot }}</div>
    </div>
</div>
