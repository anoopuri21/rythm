@props(['title', 'description' => null])

<div {{ $attributes->class(['ui-empty']) }}>
    @isset($icon)<div class="ui-empty__icon">{{ $icon }}</div>@endisset
    <h2 class="ui-empty__title">{{ $title }}</h2>
    @if($description)<p class="ui-empty__copy">{{ $description }}</p>@endif
    @isset($action)<div class="mt-2">{{ $action }}</div>@endisset
</div>
