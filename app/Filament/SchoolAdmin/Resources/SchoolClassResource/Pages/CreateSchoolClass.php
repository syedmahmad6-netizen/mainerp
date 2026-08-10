<?php

namespace App\Filament\SchoolAdmin\Resources\SchoolClassResource\Pages;

use App\Filament\SchoolAdmin\Resources\SchoolClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolClass extends CreateRecord
{
    protected static string $resource = SchoolClassResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
