<?php

namespace App\Filament\SchoolAdmin\Resources\FeeCollectionResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeCollections extends ListRecords
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Fee Record'),
        ];
    }
}
