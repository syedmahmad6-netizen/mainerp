<?php

namespace App\Filament\SchoolAdmin\Resources\ParentResource\Pages;

use App\Filament\SchoolAdmin\Resources\ParentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParents extends ListRecords
{
    protected static string $resource = ParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Parent'),
        ];
    }
}
