<?php

namespace App\Filament\SchoolAdmin\Resources\ParentResource\Pages;

use App\Filament\SchoolAdmin\Resources\ParentResource;
use App\Models\ParentProfile;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateParent extends CreateRecord
{
    protected static string $resource = ParentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $hasRealEmail = ! empty($data['email']);
            $tempPassword = Str::random(10);
            $studentIds   = $data['student_ids'] ?? [];
            $schoolId     = tenant()->getSchoolId();

            if ($hasRealEmail) {
                $email = $data['email'];
            } else {
                // Always unique — add a random suffix so repeated attempts never collide
                $email = 'parent.' . Str::slug($data['phone']) . '.' . Str::random(6)
                    . '@' . tenant()->getSchool()->subdomain . '.local';
            }

            $user = User::create([
                'school_id' => $schoolId,
                'name'      => $data['name'],
                'email'     => $email,
                'phone'     => $data['phone'],
                'password'  => $tempPassword,
                'role'      => 'parent',
                'is_active' => $hasRealEmail,
            ]);

            $profile = ParentProfile::create([
                'school_id'    => $schoolId,
                'user_id'      => $user->id,
                'cnic'         => $data['cnic']       ?? null,
                'occupation'   => $data['occupation'] ?? null,
                'relationship' => $data['relationship'],
            ]);

            // Link selected students — pass school_id explicitly for the pivot table
            if (! empty($studentIds)) {
                $syncData = collect($studentIds)->mapWithKeys(fn ($id) => [
                    $id => ['school_id' => $schoolId],
                ])->toArray();

                $profile->students()->sync($syncData);
            }

            $childNote = ! empty($studentIds)
                ? "\nLinked to " . count($studentIds) . ' child(ren).'
                : "\nNo children linked yet — you can link them anytime by editing this parent.";

            if ($hasRealEmail) {
                Notification::make()
                    ->title('Parent portal access created!')
                    ->body(
                        "Name: {$data['name']}\n" .
                        "Email: {$email}\n" .
                        "Password: {$tempPassword}" .
                        $childNote . "\n\n" .
                        '⚠️ Share these credentials with the parent.'
                    )
                    ->info()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title('Parent added successfully.')
                    ->body('No portal access created — email was not provided.' . $childNote)
                    ->success()
                    ->send();
            }

            return $profile;
        });
    }
}