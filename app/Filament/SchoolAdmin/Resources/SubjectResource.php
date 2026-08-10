<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model           = Subject::class;
    protected static ?string $navigationIcon  = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Subjects';
    protected static ?string $navigationGroup = 'Academic Setup';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Subject Name')
                ->placeholder('e.g. Mathematics, Urdu, Islamiat')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('code')
                ->label('Short Code')
                ->placeholder('MATH, URD, ISL')
                ->maxLength(20)
                ->helperText('Optional. Used in timetable and report cards.'),

            Forms\Components\ColorPicker::make('color')
                ->label('Color')
                ->helperText('Used in timetable view to identify this subject.'),

            Forms\Components\Toggle::make('is_core')
                ->label('Core Subject')
                ->default(true)
                ->helperText('Core = compulsory. Uncheck for optional/elective subjects.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->label('Subject')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_core')
                    ->label('Core')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit'   => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
