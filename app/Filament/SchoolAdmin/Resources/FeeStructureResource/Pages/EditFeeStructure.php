<?php

namespace App\Filament\SchoolAdmin\Resources\FeeStructureResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeStructure extends EditRecord
{
    protected static string $resource = FeeStructureResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
