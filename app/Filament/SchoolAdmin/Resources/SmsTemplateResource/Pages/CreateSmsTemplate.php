<?php
namespace App\Filament\SchoolAdmin\Resources\SmsTemplateResource\Pages;
use App\Filament\SchoolAdmin\Resources\SmsTemplateResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSmsTemplate extends CreateRecord {
    protected static string $resource = SmsTemplateResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}