<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-slot name="actions">
            <x-filament::button type="submit" size="lg">
                Save settings
            </x-filament::button>
        </x-slot>
    </x-filament-panels::form>
</x-filament-panels::page>
