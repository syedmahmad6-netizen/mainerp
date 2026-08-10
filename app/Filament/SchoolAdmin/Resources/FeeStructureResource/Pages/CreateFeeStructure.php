<?php

namespace App\Filament\SchoolAdmin\Resources\FeeStructureResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeStructureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeeStructure extends CreateRecord
{
    protected static string $resource = FeeStructureResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
