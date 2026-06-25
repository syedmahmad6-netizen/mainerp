<?php
namespace App\Filament\SchoolAdmin\Resources\AssignmentResource\Pages;
use App\Filament\SchoolAdmin\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditAssignment extends EditRecord {
    protected static string $resource = AssignmentResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}