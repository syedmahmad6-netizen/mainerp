<?php

namespace App\Filament\SchoolAdmin\Resources\AttendanceResource\Pages;

use App\Filament\SchoolAdmin\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Stamp who edited and why when a Principal corrects a record.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['edited_by'] = auth()->id();
        $record->update($data);
        return $record;
    }
}
