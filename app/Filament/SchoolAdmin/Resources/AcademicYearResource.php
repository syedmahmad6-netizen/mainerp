<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model           = AcademicYear::class;
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Academic Years';
    protected static ?string $navigationGroup = 'Academic Setup';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Year Name')
                ->placeholder('2025-2026')
                ->required()
                ->maxLength(20),

            Forms\Components\DatePicker::make('start_date')
                ->label('Start Date')
                ->required(),

            Forms\Components\DatePicker::make('end_date')
                ->label('End Date')
                ->required()
                ->after('start_date'),

            Forms\Components\Toggle::make('is_current')
                ->label('Current Academic Year')
                ->helperText('Marking this active will deactivate all other years automatically.')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Academic Year')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Starts')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ends')
                    ->date('d M Y'),

                Tables\Columns\IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sections_count')
                    ->label('Sections')
                    ->counts('sections')
                    ->badge()
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('set_current')
                    ->label('Set as Current')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AcademicYear $record) => ! $record->is_current)
                    ->action(fn (AcademicYear $record) => $record->update(['is_current' => true])),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'edit'   => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
