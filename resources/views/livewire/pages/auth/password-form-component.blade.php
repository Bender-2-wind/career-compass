<x-filament-panels::form wire:submit="updatePassword">
    {{ $this->form }}

    <div class="fi-form-actions">
        <div class="flex flex-row-reverse flex-wrap items-center gap-3 fi-ac">
            <x-filament::button type="submit">
                {{ __('update') }}
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::form>