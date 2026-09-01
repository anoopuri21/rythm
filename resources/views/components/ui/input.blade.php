@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

@php $id = $attributes->get('id', $name); @endphp

<div class="ui-field">
    @if($label)
        <label for="{{ $id }}" class="ui-label">{{ $label }}@if($required) <span aria-hidden="true">*</span>@endif</label>
    @endif
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ old($name, $value) }}" @required($required)
           @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif($help) aria-describedby="{{ $id }}-help" @endif
           {{ $attributes->except('id')->class(['ui-input']) }}>
    @if($error)<p id="{{ $id }}-error" class="ui-error">{{ $error }}</p>
    @elseif($help)<p id="{{ $id }}-help" class="ui-help">{{ $help }}</p>@endif
</div>
