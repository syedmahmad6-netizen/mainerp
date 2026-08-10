<?php

namespace App\Filament\SchoolAdmin\Resources\FeeTypeResource\Pages;

use App\Filament\SchoolAdmin\Resources\FeeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeType extends EditRecord
{
    protected static string $resource = FeeTypeResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
