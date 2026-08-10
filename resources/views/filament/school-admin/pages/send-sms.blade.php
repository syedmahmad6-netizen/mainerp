<x-filament-panels::page>
    @php $enabled = tenant()->getSchool()->setting('sms_enabled', false); @endphp
    @if(!$enabled)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4 text-sm text-yellow-700">
            ⚠️ SMS is not enabled. Go to <strong>School Settings → Academic & Fee Settings</strong> and turn on SMS Notifications, then add your API key.
        </div>
    @endif
    <x-filament-panels::form wire:submit="send">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>