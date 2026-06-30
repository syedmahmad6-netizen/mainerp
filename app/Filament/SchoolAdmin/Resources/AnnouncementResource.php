<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class AnnouncementResource extends Resource {
    protected static ?string $model           = Announcement::class;
    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Announcements';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int    $navigationSort  = 1;
    public static function getNavigationBadge(): ?string {
        $active = static::getModel()::where('is_archived', false)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();
        return $active > 0 ? (string) $active : null;
    }
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Announcement Details')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\RichEditor::make('body')
                    ->label('Message')->required()->columnSpanFull()
                    ->toolbarButtons(['bold','italic','underline','bulletList','orderedList','link']),
                Forms\Components\Select::make('type')->label('Type')
                    ->options(['general'=>'📢 General','urgent'=>'🚨 Urgent','holiday'=>'🎉 Holiday Notice','event'=>'📅 Event','exam_schedule'=>'📝 Exam Schedule'])
                    ->default('general')->required(),
                Forms\Components\Select::make('target_role')->label('Who sees this?')
                    ->options(['all'=>'Everyone','parents'=>'Parents Only','students'=>'Students Only','teachers'=>'Teachers Only','staff'=>'Staff Only'])
                    ->default('all')->required(),
                Forms\Components\Select::make('section_id')->label('Class / Section (leave blank for school-wide)')
                    ->options(fn() => Section::with('schoolClass')->get()->mapWithKeys(fn($s) => [$s->id => $s->schoolClass->name.' – '.$s->name]))
                    ->searchable()->nullable()->placeholder('School-Wide')
                    ->helperText('Leave blank to show to everyone. Select a section to target one class.'),
                Forms\Components\DateTimePicker::make('published_at')->label('Publish At')->default(now())->helperText('Leave as now to publish immediately.'),
                Forms\Components\DateTimePicker::make('expires_at')->label('Expires At (optional)')->helperText('Announcement auto-archives after this date.')->nullable(),
                Forms\Components\FileUpload::make('attachment')->label('Attachment (optional)')->directory('announcements')->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])->maxSize(2048),
                Forms\Components\Toggle::make('send_sms')->label('Also send as SMS')->helperText('Requires SMS module to be configured.')->default(false),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->badge()
                ->color(fn(string $state) => match($state){'general'=>'info','urgent'=>'danger','holiday'=>'success','event'=>'warning','exam_schedule'=>'primary',default=>'gray'})
                ->formatStateUsing(fn(Announcement $r) => $r->type_label),
            Tables\Columns\TextColumn::make('title')->label('Title')->searchable()->sortable()->limit(50)->weight('bold'),
            Tables\Columns\TextColumn::make('target_role')->label('Audience')->badge()->color('gray')
                ->formatStateUsing(fn($s) => match($s){'all'=>'Everyone','parents'=>'Parents','students'=>'Students','teachers'=>'Teachers','staff'=>'Staff',default=>$s}),
            Tables\Columns\TextColumn::make('scope_attribute')->label('Scope')
                ->getStateUsing(fn(Announcement $r) => $r->scope)->badge()->color('primary'),
            Tables\Columns\TextColumn::make('createdBy.name')->label('Posted By')->toggleable(),
            Tables\Columns\TextColumn::make('published_at')->label('Published')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('expires_at')->label('Expires')->dateTime('d M Y')->default('Never')->toggleable(),
            Tables\Columns\TextColumn::make('reads_count')->label('Read By')->counts('reads')->badge()->color('success'),
            Tables\Columns\IconColumn::make('is_archived')->label('Archived')->boolean()->toggleable(isToggledHiddenByDefault:true),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')->options(['general'=>'General','urgent'=>'Urgent','holiday'=>'Holiday','event'=>'Event','exam_schedule'=>'Exam Schedule']),
            Tables\Filters\SelectFilter::make('target_role')->label('Audience')->options(['all'=>'All','parents'=>'Parents','students'=>'Students','teachers'=>'Teachers']),
            Tables\Filters\TernaryFilter::make('is_archived')->label('Archived'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('archive')
                ->label('Archive')->icon('heroicon-o-archive-box')->color('warning')
                ->requiresConfirmation()
                ->visible(fn(Announcement $r) => !$r->is_archived)
                ->action(fn(Announcement $r) => $r->update(['is_archived'=>true])),
            Tables\Actions\Action::make('unarchive')
                ->label('Restore')->icon('heroicon-o-archive-box-arrow-down')->color('success')
                ->visible(fn(Announcement $r) => $r->is_archived)
                ->action(fn(Announcement $r) => $r->update(['is_archived'=>false])),
            Tables\Actions\DeleteAction::make(),
        ])->defaultSort('published_at','desc');
    }
    public static function getPages(): array {
        return [
            'index'  => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view'   => Pages\ViewAnnouncement::route('/{record}'),
            'edit'   => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}