<?php
namespace App\Filament\SchoolAdmin\Resources\AnnouncementResource\Pages;
use App\Filament\SchoolAdmin\Resources\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
class CreateAnnouncement extends CreateRecord {
    protected static string $resource = AnnouncementResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data['created_by']   = auth()->id();
        $data['school_id']    = tenant()->getSchoolId();
        $data['published_at'] = $data['published_at'] ?? now();
        return $data;
    }
}