<?php

namespace App\Filament\SchoolAdmin\Resources\SchoolClassResource\Pages;

use App\Filament\SchoolAdmin\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add Class')];
    }
}
