<?php
namespace App\Filament\SchoolAdmin\Resources\SmsTemplateResource\Pages;
use App\Filament\SchoolAdmin\Resources\SmsTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListSmsTemplates extends ListRecords {
    protected static string $resource = SmsTemplateResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('Add Template')]; }
}