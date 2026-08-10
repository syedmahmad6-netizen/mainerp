<?php

namespace App\Filament\SchoolAdmin\Resources\ParentResource\Pages;

use App\Filament\SchoolAdmin\Resources\ParentResource;
use App\Models\ParentProfile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditParent extends EditRecord
{
    protected static string $resource = ParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        $data['name']  = $user?->name;
        $data['email'] = str_contains($user?->email ?? '', '.local') ? '' : $user?->email;
        $data['phone'] = $user?->phone;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ParentProfile $record */
        return DB::transaction(function () use ($record, $data) {

            $updateUserData = [
                'name'  => $data['name'],
                'phone' => $data['phone'],
            ];

            // Only update email if a real one was provided
            if (! empty($data['email'])) {
                $updateUserData['email']     = $data['email'];
                $updateUserData['is_active'] = true;
            }

            $record->user->update($updateUserData);

            $record->update([
                'cnic'         => $data['cnic']         ?? null,
                'occupation'   => $data['occupation']   ?? null,
                'relationship' => $data['relationship'],
            ]);

            return $record;
        });
    }
}
