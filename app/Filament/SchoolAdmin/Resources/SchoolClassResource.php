<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolClassResource extends Resource
{
    protected static ?string $model           = SchoolClass::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Classes & Grades';
    protected static ?string $navigationGroup = 'Academic Setup';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Class Name')
                ->placeholder('e.g. Grade 5, Class 9, Nursery')
                ->required()
                ->maxLength(50),

            Forms\Components\Select::make('level')
                ->label('School Level')
                ->options([
                    'pre_primary'      => 'Pre-Primary (Nursery / KG / Prep)',
                    'primary'          => 'Primary (Grade 1–5)',
                    'middle'           => 'Middle (Grade 6–8)',
                    'secondary'        => 'Secondary / Matric (Grade 9–10)',
                    'higher_secondary' => 'Higher Secondary / Inter (Grade 11–12)',
                ])
                ->required(),

            Forms\Components\TextInput::make('numeric_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0)
                ->helperText('Lower number appears first. Nursery = 0, KG = 1, Grade 1 = 3, etc.')
                ->minValue(0),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pre_primary'      => 'Pre-Primary',
                        'primary'          => 'Primary',
                        'middle'           => 'Middle',
                        'secondary'        => 'Secondary',
                        'higher_secondary' => 'Higher Secondary',
                        default            => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pre_primary'      => 'info',
                        'primary'          => 'success',
                        'middle'           => 'warning',
                        'secondary'        => 'danger',
                        'higher_secondary' => 'gray',
                        default            => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sections_count')
                    ->label('Sections')
                    ->counts('sections')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('numeric_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('numeric_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit'   => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
