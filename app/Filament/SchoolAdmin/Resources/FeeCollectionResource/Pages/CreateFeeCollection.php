<?php

namespace App\Filament\SchoolAdmin\Resources\FeeCollectionResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeCollectionResource;
use App\Models\FeeCollection;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFeeCollection extends CreateRecord
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Auto-calculate balance before saving.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $data['balance'] = ($data['amount_due'] - ($data['discount'] ?? 0)) - ($data['amount_paid'] ?? 0);

        if (($data['amount_paid'] ?? 0) > 0) {
            $data['receipt_no'] = FeeCollection::generateReceiptNumber();
            $data['collected_by'] = auth()->id();
        }

        return FeeCollection::create($data);
    }
}
