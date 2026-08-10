<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\ExamTypeResource\Pages;
use App\Models\ExamType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ExamTypeResource extends Resource {
    protected static ?string $model           = ExamType::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Exam Types';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 7;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Exam Type Name')
                ->placeholder('e.g. Monthly Test, Mid Term, Final Term, Quiz')
                ->required()->maxLength(100),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Exam Type')->sortable()->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('exams_count')->label('Exams')->counts('exams')->badge()->color('primary'),
        ])->actions([Tables\Actions\EditAction::make(),Tables\Actions\DeleteAction::make()])
          ->defaultSort('name');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListExamTypes::route('/'),'create'=>Pages\CreateExamType::route('/create'),'edit'=>Pages\EditExamType::route('/{record}/edit')];
    }
}