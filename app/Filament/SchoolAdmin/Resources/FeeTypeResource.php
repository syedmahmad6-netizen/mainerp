<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\FeeTypeResource\Pages;
use App\Models\FeeType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeeTypeResource extends Resource
{
    protected static ?string $model           = FeeType::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Fee Types';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Fee Type Name')
                ->placeholder('e.g. Tuition Fee, Exam Fee, Sports Fee')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(2)
                ->maxLength(255),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fee Type')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->default('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('fee_structures_count')
                    ->label('Structures')
                    ->counts('feeStructures')
                    ->badge()
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (FeeType $record) {
                        // Prevent deletion if fee structures exist
                        if ($record->feeStructures()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot delete')
                                ->body('This fee type has fee structures assigned. Remove structures first.')
                                ->danger()
                                ->send();
                            $this->halt();
                        }
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeeTypes::route('/'),
            'create' => Pages\CreateFeeType::route('/create'),
            'edit'   => Pages\EditFeeType::route('/{record}/edit'),
        ];
    }
}
