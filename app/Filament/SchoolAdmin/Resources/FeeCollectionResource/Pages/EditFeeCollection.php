<?php

namespace App\Filament\SchoolAdmin\Resources\FeeCollectionResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFeeCollection extends EditRecord
{
    protected static string $resource = FeeCollectionResource::class;

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
        // Recalculate balance on every edit
        $data['balance'] = max(0,
            ($data['amount_due'] - ($data['discount'] ?? 0) + ($data['fine'] ?? 0))
            - ($data['amount_paid'] ?? 0)
        );

        $record->update($data);
        return $record;
    }
}
