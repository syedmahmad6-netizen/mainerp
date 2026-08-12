<?php

namespace App\Filament\SchoolAdmin\Resources\ParentResource\Pages;

use App\Filament\SchoolAdmin\Resources\ParentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditParent extends EditRecord
{
    protected static string $resource = ParentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name']  = $this->record->user->name  ?? null;
        $data['email'] = $this->record->user->email ?? null;
        $data['phone'] = $this->record->user->phone ?? null;
        $data['student_ids'] = $this->record->students()->pluck('student_profiles.id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $schoolId = tenant()->getSchoolId();

        $record->user()->update([
            'name'  => $data['name'],
            'email' => $data['email'] ?? $record->user->email,
            'phone' => $data['phone'],
        ]);

        $record->update([
            'cnic'         => $data['cnic']       ?? null,
            'occupation'   => $data['occupation'] ?? null,
            'relationship' => $data['relationship'],
        ]);

        // Sync linked students — pass school_id explicitly for the pivot table
        $studentIds = $data['student_ids'] ?? [];
        $syncData = collect($studentIds)->mapWithKeys(fn ($id) => [
            $id => ['school_id' => $schoolId],
        ])->toArray();

        $record->students()->sync($syncData);

        return $record;
    }
}
