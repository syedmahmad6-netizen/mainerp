<?php
namespace App\Filament\SchoolAdmin\Resources\AnnouncementResource\Pages;
use App\Filament\SchoolAdmin\Resources\AnnouncementResource;
use App\Models\Announcement;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
class ViewAnnouncement extends ViewRecord {
    protected static string $resource = AnnouncementResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Section::make()->schema([
                TextEntry::make('type')->badge()
                    ->color(fn(string $s) => match($s){'general'=>'info','urgent'=>'danger','holiday'=>'success','event'=>'warning','exam_schedule'=>'primary',default=>'gray'})
                    ->formatStateUsing(fn(Announcement $r) => $r->type_label),
                TextEntry::make('target_role')->label('Audience')
                    ->formatStateUsing(fn($state) => match($state){'all'=>'Everyone','parents'=>'Parents Only','students'=>'Students Only','teachers'=>'Teachers Only','staff'=>'Staff Only',default=>$state}),
                TextEntry::make('scope_attribute')->label('Scope')->getStateUsing(fn(Announcement $r) => $r->scope),
                TextEntry::make('createdBy.name')->label('Posted By'),
                TextEntry::make('published_at')->label('Published')->dateTime('d M Y, h:i A'),
                TextEntry::make('expires_at')->label('Expires')->dateTime('d M Y')->default('No expiry'),
            ])->columns(3),
            Section::make('Message')->schema([
                TextEntry::make('title')->label('Title')->weight(\Filament\Support\Enums\FontWeight::Bold)->size(TextEntry\TextEntrySize::Large),
                TextEntry::make('body')->label('')->html()->columnSpanFull(),
            ]),
            Section::make('Engagement')->columns(2)->schema([
                TextEntry::make('reads_count')->label('Read by')->getStateUsing(fn(Announcement $r) => $r->reads()->count().' users'),
                IconEntry::make('send_sms')->label('SMS sent')->boolean(),
            ]),
        ]);
    }
}