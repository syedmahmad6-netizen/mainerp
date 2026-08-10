<?php

namespace App\Filament\SchoolAdmin\Resources\FeeTypeResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeTypes extends ListRecords
{
    protected static string $resource = FeeTypeResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add Fee Type')];
    }
}
