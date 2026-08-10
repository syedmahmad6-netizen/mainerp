<?php
namespace App\Filament\SchoolAdmin\Resources\ExamResource\Pages;
use App\Filament\SchoolAdmin\Resources\ExamResource;
use Filament\Resources\Pages\CreateRecord;
class CreateExam extends CreateRecord {
    protected static string $resource = ExamResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}