<?php

namespace App\Filament\SchoolAdmin\Resources\TimetableResource\Pages;

use App\Filament\SchoolAdmin\Resources\TimetableResource;
use App\Models\Timetable;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTimetable extends CreateRecord
{
    protected static string $resource = TimetableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // ── Conflict Detection ────────────────────────────────────────────

        // Rule 1: Same teacher cannot be in two places at the same time
        $teacherConflict = Timetable::where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('section_id', '!=', $data['section_id'])
            ->with('section.schoolClass')
            ->first();

        if ($teacherConflict) {
            $className = $teacherConflict->section->schoolClass->name ?? 'another class';
            $sectionName = $teacherConflict->section->name ?? '';
            $day = Timetable::dayName($data['day_of_week']);

            Notification::make()
                ->title('⚠️ Teacher Conflict Detected!')
                ->body(
                    "This teacher is already assigned to {$className} – {$sectionName} " .
                    "on {$day} at this time slot.\n\n" .
                    "Please choose a different teacher or time slot."
                )
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        // Rule 2: Same section slot already filled (caught by DB unique constraint,
        // but we give a friendly message here too)
        $slotTaken = Timetable::where('section_id', $data['section_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->exists();

        if ($slotTaken) {
            Notification::make()
                ->title('Slot Already Assigned')
                ->body('This section already has a subject assigned to this period. Edit the existing entry instead.')
                ->warning()
                ->persistent()
                ->send();

            $this->halt();
        }

        return Timetable::create($data);
    }
}
