<?php
namespace App\Filament\SchoolAdmin\Resources\ExamTypeResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditExamType extends EditRecord {
    protected static string $resource = ExamTypeResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}