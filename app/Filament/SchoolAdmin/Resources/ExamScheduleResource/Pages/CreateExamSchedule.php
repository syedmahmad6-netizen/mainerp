<?php
namespace App\Filament\SchoolAdmin\Resources\ExamScheduleResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamScheduleResource;
use Filament\Resources\Pages\CreateRecord;
class CreateExamSchedule extends CreateRecord {
    protected static string $resource = ExamScheduleResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}