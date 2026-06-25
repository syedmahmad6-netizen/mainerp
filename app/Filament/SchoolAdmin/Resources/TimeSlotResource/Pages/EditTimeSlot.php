<?php
namespace App\Filament\SchoolAdmin\Resources\TimeSlotResource\Pages;
use App\Filament\SchoolAdmin\Resources\TimeSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditTimeSlot extends EditRecord {
    protected static string $resource = TimeSlotResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
