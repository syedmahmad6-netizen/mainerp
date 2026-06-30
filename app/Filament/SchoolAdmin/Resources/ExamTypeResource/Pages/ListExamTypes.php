<?php
namespace App\Filament\SchoolAdmin\Resources\ExamTypeResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListExamTypes extends ListRecords {
    protected static string $resource = ExamTypeResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('Add Exam Type')]; }
}