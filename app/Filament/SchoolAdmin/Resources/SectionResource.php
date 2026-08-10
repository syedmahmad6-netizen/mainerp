<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\SectionResource\Pages;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    protected static ?string $model           = Section::class;
    protected static ?string $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Sections';
    protected static ?string $navigationGroup = 'Academic Setup';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')->native()
                ->label('Academic Year')
                ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                ->required()
                ->searchable(),

            Forms\Components\Select::make('school_class_id')
                ->label('Class / Grade')
                ->options(fn () =>
                    SchoolClass::orderBy('numeric_order')
                        ->get()
                        ->pluck('name', 'id')
                )
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->label('Section Name')
                ->placeholder('A, B, C or Blue, Stars...')
                ->required()
                ->maxLength(10),

            Forms\Components\TextInput::make('capacity')
                ->label('Max Students')
                ->numeric()
                ->default(40)
                ->minValue(1)
                ->maxValue(100),

            Forms\Components\Select::make('class_teacher_id')->native()
                ->label('Class Teacher')
                ->options(fn () =>
                    User::where('role', 'teacher')
                        ->get()
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->nullable()
                ->placeholder('Assign later'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Section')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('classTeacher.name')
                    ->label('Class Teacher')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(fn () => AcademicYear::all()->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('school_class_id')
                    ->label('Class')
                    ->options(fn () =>
                        SchoolClass::orderBy('numeric_order')
                            ->get()
                            ->pluck('name', 'id')
                    ),
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
            'index'  => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit'   => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
