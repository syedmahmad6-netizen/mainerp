<?php
namespace App\Filament\SchoolAdmin\Resources\ExamScheduleResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditExamSchedule extends EditRecord {
    protected static string $resource = ExamScheduleResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}