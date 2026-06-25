<?php

namespace App\Filament\SchoolAdmin\Resources\TeacherResource\Pages;

use App\Filament\SchoolAdmin\Resources\TeacherResource;
use App\Models\TeacherProfile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('This will permanently delete the teacher record and their user account.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Pre-fill form with data from both User and TeacherProfile.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        $data['name']          = $user?->name;
        $data['email']         = $user?->email;
        $data['phone']         = $user?->phone;
        $data['profile_photo'] = $user?->profile_photo;

        // Pre-select assigned subjects for the checkbox list
        $data['subjects'] = $this->record->subjects()->pluck('subjects.id')->toArray();

        // If employee_id matches auto format, default to auto
        $data['custom_employee_id'] = false;

        return $data;
    }

    /**
     * Update User + TeacherProfile + subject assignments.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TeacherProfile $record */
        return DB::transaction(function () use ($record, $data) {

            // ── Update User account ───────────────────────────────────────
            $record->user->update([
                'name'          => $data['name'],
                'email'         => $data['email']         ?? $record->user->email,
                'phone'         => $data['phone']         ?? $record->user->phone,
                'profile_photo' => $data['profile_photo'] ?? $record->user->profile_photo,
            ]);

            // ── Update TeacherProfile ─────────────────────────────────────
            $record->update([
                'employee_id'     => $data['custom_employee_id']
                    ? $data['employee_id']
                    : $record->employee_id,
                'cnic'            => $data['cnic'],
                'qualification'   => $data['qualification']  ?? null,
                'specialization'  => $data['specialization'] ?? null,
                'date_of_birth'   => $data['date_of_birth']  ?? null,
                'gender'          => $data['gender']         ?? null,
                'joining_date'    => $data['joining_date']   ?? $record->joining_date,
                'salary'          => $data['salary']         ?? null,
                'employment_type' => $data['employment_type'],
                'status'          => $data['status'],
            ]);

            // ── Sync subject assignments ──────────────────────────────────
            // sync() removes old + adds new in one call
            $subjectData = collect($data['subjects'] ?? [])
                ->mapWithKeys(fn ($id) => [$id => ['school_id' => tenant()->getSchoolId()]])
                ->toArray();

            $record->subjects()->sync($subjectData);

            return $record;
        });
    }
}
