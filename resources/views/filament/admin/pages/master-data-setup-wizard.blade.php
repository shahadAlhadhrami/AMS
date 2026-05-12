<x-filament-panels::page>
    <form wire:submit.prevent="finishSetup">
        {{ $this->form }}
    </form>
</x-filament-panels::page>
