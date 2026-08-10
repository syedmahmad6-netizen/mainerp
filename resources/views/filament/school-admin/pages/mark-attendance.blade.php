<x-filament-panels::page>
    {{-- Tip shown once students are loaded --}}
    @if(!empty($this->data['attendance']))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
            ✅ <strong>{{ count($this->data['attendance']) }} students loaded.</strong>
            Default status is <strong>Present</strong>. Change only the absent/late students.
        </div>
    @endif

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
            :full-width="false"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
