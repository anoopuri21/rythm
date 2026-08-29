@props(['ratio' => 'product', 'contain' => false])

<div {{ $attributes->class(['ui-media', 'ui-media--' . $ratio, 'ui-media--contain' => $contain]) }}>
    {{ $slot }}
</div>
