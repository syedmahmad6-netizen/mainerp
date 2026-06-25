<?php
namespace App\Filament\SchoolAdmin\Resources\AssignmentResource\Pages;
use App\Filament\SchoolAdmin\Resources\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateAssignment extends CreateRecord {
    protected static string $resource = AssignmentResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data['teacher_id'] = $data['teacher_id'] ?? auth()->id();
        return $data;
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}