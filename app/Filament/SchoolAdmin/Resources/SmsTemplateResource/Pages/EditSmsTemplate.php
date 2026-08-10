<?php
namespace App\Filament\SchoolAdmin\Resources\SmsTemplateResource\Pages;
use App\Filament\SchoolAdmin\Resources\SmsTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditSmsTemplate extends EditRecord {
    protected static string $resource = SmsTemplateResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}