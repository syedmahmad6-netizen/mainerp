<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\FeeStructureResource\Pages;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeeStructureResource extends Resource
{
    protected static ?string $model           = FeeStructure::class;
    protected static ?string $navigationIcon  = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Fee Structure';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Fee Structure Details')
                ->description('Define how much a class owes for a specific fee type.')
                ->schema([
                    Forms\Components\Select::make('academic_year_id')
                        ->label('Academic Year')
                        ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                        ->default(fn () => AcademicYear::where('is_current', true)->value('id'))
                        ->required(),

                    Forms\Components\Select::make('school_class_id')
                        ->label('Class')
                        ->options(fn () =>
                            SchoolClass::orderBy('numeric_order')->get()->pluck('name', 'id')
                        )
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('fee_type_id')
                        ->label('Fee Type')
                        ->options(fn () => FeeType::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('frequency')
                        ->label('Frequency')
                        ->options([
                            'monthly'   => 'Monthly',
                            'quarterly' => 'Quarterly (every 3 months)',
                            'annual'    => 'Annual (once per year)',
                            'one_time'  => 'One-Time (e.g. admission)',
                        ])
                        ->required()
                        ->default('monthly'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Amount (PKR)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->required()
                        ->minValue(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('feeType.name')
                    ->label('Fee Type')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'monthly'   => 'success',
                        'quarterly' => 'warning',
                        'annual'    => 'primary',
                        'one_time'  => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annual'    => 'Annual',
                        'one_time'  => 'One-Time',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Year')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(fn () => AcademicYear::all()->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('school_class_id')
                    ->label('Class')
                    ->options(fn () =>
                        SchoolClass::orderBy('numeric_order')->get()->pluck('name', 'id')
                    ),

                Tables\Filters\SelectFilter::make('frequency')
                    ->options([
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'annual'    => 'Annual',
                        'one_time'  => 'One-Time',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('school_class_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeeStructures::route('/'),
            'create' => Pages\CreateFeeStructure::route('/create'),
            'edit'   => Pages\EditFeeStructure::route('/{record}/edit'),
        ];
    }
}
