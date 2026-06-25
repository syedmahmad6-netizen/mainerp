<?php

namespace App\Filament\SchoolAdmin\Resources\StudentResource\Pages;

use App\Filament\SchoolAdmin\Resources\StudentResource;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function getRedirectUrl(): string
    {
        // After creating, go to the student's profile view
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Override the default record creation.
     * We need to:
     *  1. Create the User account (for authentication)
     *  2. Create the StudentProfile (for academic data)
     *  Both must succeed or both must fail — hence DB::transaction.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            // ── Step 1: Determine admission number ────────────────────────
            $admissionNumber = $data['custom_admission_number']
                ? $data['admission_number']
                : StudentProfile::generateAdmissionNumber();

            // ── Step 2: Check if class level needs active student login ───
            $section         = Section::with('schoolClass')->find($data['current_section_id']);
            $level           = $section?->schoolClass?->level;
            $needsActiveLogin = in_array($level, ['middle', 'secondary', 'higher_secondary']);

            // ── Step 3: Determine email ───────────────────────────────────
            // If provided by staff → use it.
            // Otherwise → generate a non-real internal email (won't be used for lower grades)
            $email = ! empty($data['email'])
                ? $data['email']
                : 'student.' . strtolower(Str::slug($admissionNumber)) . '@' . tenant()->getSchool()->subdomain . '.local';

            $tempPassword = Str::random(10);

            // ── Step 4: Create User account ───────────────────────────────
            $user = User::create([
                'school_id'     => tenant()->getSchoolId(),
                'name'          => $data['name'],
                'email'         => $email,
                'phone'         => $data['phone'] ?? null,
                'password'      => $tempPassword,
                'profile_photo' => $data['profile_photo'] ?? null,
                'role'          => 'student',
                // Active = true for Grade 6+, false for lower grades
                'is_active'     => $needsActiveLogin,
            ]);

            // ── Step 5: Create StudentProfile ─────────────────────────────
            $profile = StudentProfile::create([
                'school_id'          => tenant()->getSchoolId(),
                'user_id'            => $user->id,
                'father_name'        => $data['father_name'],
                'admission_number'   => $admissionNumber,
                'roll_number'        => $data['roll_number']     ?? null,
                'date_of_birth'      => $data['date_of_birth']   ?? null,
                'gender'             => $data['gender']          ?? null,
                'blood_group'        => $data['blood_group']     ?? null,
                'b_form_number'      => $data['b_form_number']   ?? null,
                'address'            => $data['address']         ?? null,
                'city'               => $data['city']            ?? null,
                'admission_date'     => $data['admission_date']  ?? today(),
                'current_section_id' => $data['current_section_id'],
                'status'             => 'active',
            ]);

            // ── Step 6: Notify with credentials (Grade 6+ only) ──────────
            if ($needsActiveLogin) {
                Notification::make()
                    ->title("Portal access created for {$data['name']}")
                    ->body(
                        "Admission No: {$admissionNumber}\n" .
                        "Email: {$email}\n" .
                        "Password: {$tempPassword}\n\n" .
                        '⚠️ Note these down and share with the student/parent.'
                    )
                    ->info()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title("Student registered successfully!")
                    ->body("Admission No: {$admissionNumber}")
                    ->success()
                    ->send();
            }

            return $profile;
        });
    }
}
