<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-filament::button type="submit" size="md">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
