<?php
namespace App\Filament\SchoolAdmin\Pages;
use App\Models\SmsLog;
use Filament\Pages\Page;
class SmsLogs extends Page {
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'SMS Logs';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.school-admin.pages.sms-logs';
    public array $logs = [];
    public function mount(): void {
        $this->logs = SmsLog::latest()->take(100)->get()->toArray();
    }
}