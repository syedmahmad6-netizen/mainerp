<?php

namespace App\Filament\SchoolAdmin\Resources\StudentResource\Pages;

use App\Filament\SchoolAdmin\Resources\StudentResource;
use App\Models\StudentProfile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('This will permanently delete the student record and their user account. This cannot be undone.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Pre-fill the form with existing data from both User and StudentProfile.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        // Merge User fields into the form data
        $data['name']          = $user?->name;
        $data['email']         = $user?->email;
        $data['profile_photo'] = $user?->profile_photo;

        // Set the admission toggle based on whether they have a non-auto number
        // (if number matches auto-format, default to auto; otherwise manual)
        $data['custom_admission_number'] = false;

        return $data;
    }

    /**
     * Override update to handle both User and StudentProfile tables.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var StudentProfile $record */
        return DB::transaction(function () use ($record, $data) {

            // ── Update the User account ───────────────────────────────────
            $record->user->update([
                'name'          => $data['name'],
                'profile_photo' => $data['profile_photo'] ?? $record->user->profile_photo,
                // Only update email if it was changed to a real email
                'email' => ! empty($data['email']) ? $data['email'] : $record->user->email,
            ]);

            // ── Update the StudentProfile ─────────────────────────────────
            $record->update([
                'father_name'        => $data['father_name'],
                'admission_number'   => $data['custom_admission_number']
                    ? $data['admission_number']
                    : $record->admission_number, // Keep existing if not changing
                'roll_number'        => $data['roll_number']       ?? null,
                'date_of_birth'      => $data['date_of_birth']     ?? null,
                'gender'             => $data['gender']            ?? null,
                'blood_group'        => $data['blood_group']       ?? null,
                'b_form_number'      => $data['b_form_number']     ?? null,
                'address'            => $data['address']           ?? null,
                'city'               => $data['city']              ?? null,
                'admission_date'     => $data['admission_date']    ?? $record->admission_date,
                'current_section_id' => $data['current_section_id'],
            ]);

            return $record;
        });
    }
}
