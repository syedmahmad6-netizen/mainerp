<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\SmsTemplateResource\Pages;
use App\Models\SmsTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class SmsTemplateResource extends Resource {
    protected static ?string $model           = SmsTemplate::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'SMS Templates';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int    $navigationSort  = 2;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Template Name')->required()->maxLength(100)
                ->placeholder('e.g. Fee Reminder, Absence Alert'),
            Forms\Components\Textarea::make('message_body')->label('Message Text')->required()->rows(4)
                ->helperText('Use {student_name}, {amount}, {date}, {school_name}, {percentage} as variables.')
                ->columnSpanFull(),
            Forms\Components\TagsInput::make('variables')->label('Variables Used')
                ->placeholder('Add variable name...')
                ->helperText('List the variable names used in the message above.')
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Template')->sortable()->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('message_body')->label('Preview')->limit(60)->wrap(),
            Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(),Tables\Actions\DeleteAction::make()])
          ->defaultSort('name');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListSmsTemplates::route('/'),'create'=>Pages\CreateSmsTemplate::route('/create'),'edit'=>Pages\EditSmsTemplate::route('/{record}/edit')];
    }
}