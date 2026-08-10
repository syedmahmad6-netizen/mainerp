<?php
namespace App\Filament\SchoolAdmin\Resources\ExamTypeResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamTypeResource;
use Filament\Resources\Pages\CreateRecord;
class CreateExamType extends CreateRecord {
    protected static string $resource = ExamTypeResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}