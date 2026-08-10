<?php

namespace App\Filament\SchoolAdmin\Resources\TimetableResource\Pages;

use App\Filament\SchoolAdmin\Resources\TimetableResource;
use App\Models\Timetable;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTimetable extends EditRecord
{
    protected static string $resource = TimetableResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Check teacher conflict — exclude this record from the check
        $teacherConflict = Timetable::where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('section_id', '!=', $data['section_id'])
            ->where('id', '!=', $record->id)
            ->with('section.schoolClass')
            ->first();

        if ($teacherConflict) {
            $className   = $teacherConflict->section->schoolClass->name ?? 'another class';
            $sectionName = $teacherConflict->section->name ?? '';
            $day         = Timetable::dayName($data['day_of_week']);

            Notification::make()
                ->title('⚠️ Teacher Conflict Detected!')
                ->body(
                    "This teacher is already assigned to {$className} – {$sectionName} " .
                    "on {$day} at this time. Choose a different teacher or time."
                )
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        $record->update($data);
        return $record;
    }
}
