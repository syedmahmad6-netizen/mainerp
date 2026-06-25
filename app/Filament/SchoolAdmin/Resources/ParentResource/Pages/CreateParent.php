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

            // If no email given, generate internal email (portal won't be active)
            $email = $hasRealEmail
                ? $data['email']
                : 'parent.' . Str::slug($data['phone']) . '@' . tenant()->getSchool()->subdomain . '.local';

            // Create User account
            $user = User::create([
                'school_id' => tenant()->getSchoolId(),
                'name'      => $data['name'],
                'email'     => $email,
                'phone'     => $data['phone'],
                'password'  => $tempPassword,
                'role'      => 'parent',
                'is_active' => $hasRealEmail,
            ]);

            // Create ParentProfile
            $profile = ParentProfile::create([
                'school_id'    => tenant()->getSchoolId(),
                'user_id'      => $user->id,
                'cnic'         => $data['cnic']       ?? null,
                'occupation'   => $data['occupation'] ?? null,
                'relationship' => $data['relationship'],
            ]);

            // Show credentials if portal access was created
            if ($hasRealEmail) {
                Notification::make()
                    ->title('Parent portal access created!')
                    ->body(
                        "Name: {$data['name']}\n" .
                        "Email: {$email}\n" .
                        "Password: {$tempPassword}\n\n" .
                        '⚠️ Share these credentials with the parent.'
                    )
                    ->info()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title('Parent added successfully.')
                    ->body('No portal access created — email was not provided.')
                    ->success()
                    ->send();
            }

            return $profile;
        });
    }
}
