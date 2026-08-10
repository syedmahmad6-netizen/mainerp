<?php

namespace App\Filament\SchoolAdmin\Resources\TimetableResource\Pages;

use App\Filament\SchoolAdmin\Resources\TimetableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimetable extends ListRecords
{
    protected static string $resource = TimetableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Timetable Entry'),
        ];
    }
}
