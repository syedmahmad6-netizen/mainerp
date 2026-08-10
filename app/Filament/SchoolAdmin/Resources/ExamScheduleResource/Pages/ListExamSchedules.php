<?php
namespace App\Filament\SchoolAdmin\Resources\ExamScheduleResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListExamSchedules extends ListRecords {
    protected static string $resource = ExamScheduleResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('Add Schedule Entry')]; }
}