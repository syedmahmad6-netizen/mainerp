<?php

namespace App\Filament\SchoolAdmin\Resources\FeeStructureResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeStructures extends ListRecords
{
    protected static string $resource = FeeStructureResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add Fee Structure')];
    }
}
