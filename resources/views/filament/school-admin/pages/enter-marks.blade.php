<x-filament-panels::page>
    @if(!empty($this->data["marks"]))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            Students loaded: <strong>{{ count($this->data["marks"]) }}</strong>.
            Total marks: <strong>{{ $this->totalMarks }}</strong> |
            Passing marks: <strong>{{ $this->passingMarks }}</strong>.
            Grade is calculated automatically.
        </div>
    @endif
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>