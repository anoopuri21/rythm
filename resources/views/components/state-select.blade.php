@props([
    'name' => 'state',
    'value' => '',
    'required' => true,
    'wireModel' => null,
])

@php
    $states = \App\Support\IndiaStates::NAMES;
    $current = old($name, $value);
@endphp

<select
    name="{{ $name }}"
    @if($wireModel) wire:model="{{ $wireModel }}" @endif
    @required($required)
    {{ $attributes->class('h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25') }}
>
    <option value="">Choose state</option>
    @foreach($states as $state)
        <option value="{{ $state }}" @selected($current === $state)>{{ $state }}</option>
    @endforeach
</select>
