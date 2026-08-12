<?php

namespace App\Filament\SchoolAdmin\Resources\TeacherResource\Pages;

use App\Filament\SchoolAdmin\Resources\TeacherResource;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Create User + TeacherProfile + subject assignments atomically.
     * All teachers receive active portal login credentials.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            // ── Step 1: Employee ID ───────────────────────────────────────
            $employeeId = $data['custom_employee_id']
                ? $data['employee_id']
                : TeacherProfile::generateEmployeeId();

            // ── Step 2: Email & password ──────────────────────────────────
// Real email if provided, otherwise auto-generate one for portal login
$email = ! empty($data['email'])
    ? $data['email']
    : Str::slug($data['name']) . '.' . Str::random(4) . '@' . tenant()->getSchool()->subdomain . '.local';

$tempPassword = Str::random(10);

            // ── Step 3: Create User account ───────────────────────────────
            $user = User::create([
                'school_id'     => tenant()->getSchoolId(),
                'name'          => $data['name'],
                'email'         => $email,
                'phone'         => $data['phone'],
                'password'      => $tempPassword,
                'profile_photo' => $data['profile_photo'] ?? null,
                'role'          => 'teacher',
                'is_active'     => true, // All teachers get active login
            ]);

            // ── Step 4: Create TeacherProfile ─────────────────────────────
            $profile = TeacherProfile::create([
                'school_id'        => tenant()->getSchoolId(),
                'user_id'          => $user->id,
                'employee_id'      => $employeeId,
                'cnic'             => $data['cnic'],
                'qualification'    => $data['qualification']   ?? null,
                'specialization'   => $data['specialization']  ?? null,
                'date_of_birth'    => $data['date_of_birth']   ?? null,
                'gender'           => $data['gender']          ?? null,
                'joining_date'     => $data['joining_date']    ?? today(),
                'salary'           => $data['salary']          ?? null,
                'employment_type'  => $data['employment_type'] ?? 'permanent',
                'status'           => $data['status']          ?? 'active',
            ]);

            // ── Step 5: Assign subjects ───────────────────────────────────
            if (! empty($data['subjects'])) {
                $subjectData = collect($data['subjects'])
                    ->mapWithKeys(fn ($id) => [$id => ['school_id' => tenant()->getSchoolId()]])
                    ->toArray();

                $profile->subjects()->attach($subjectData);
            }

            // ── Step 6: Show credentials ──────────────────────────────────
            Notification::make()
                ->title("Teacher account created — {$data['name']}")
                ->body(
                    "Employee ID: {$employeeId}\n" .
                    "Portal Email: {$email}\n" .
                    "Password: {$tempPassword}\n\n" .
                    '⚠️ Share these credentials with the teacher.'
                )
                ->info()
                ->persistent()
                ->send();

            return $profile;
        });
    }
}
