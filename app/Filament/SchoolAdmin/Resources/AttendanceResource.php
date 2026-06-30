<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model           = Attendance::class;
    protected static ?string $navigationIcon  = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Attendance Records';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 3;

    // ─────────────────────────────────────────────────────────────────────
    // FORM  (used for Principal edits / manual corrections)
    // ─────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Student')
                ->options(fn () =>
                    StudentProfile::with('user')
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => ($s->admission_number ?? '') . ' — ' . ($s->user->name ?? ''),
                        ])
                )
                ->searchable()
                ->required(),

            Forms\Components\Select::make('section_id')
                ->label('Section')
                ->options(fn () =>
                    Section::with('schoolClass')
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => $s->schoolClass->name . ' – ' . $s->name,
                        ])
                )
                ->required(),

            Forms\Components\DatePicker::make('date')
                ->label('Date')
                ->required()
                ->maxDate(today()),

            Forms\Components\Select::make('status')
                ->options([
                    'present' => 'Present',
                    'absent'  => 'Absent',
                    'late'    => 'Late',
                    'leave'   => 'Leave',
                ])
                ->required(),

            Forms\Components\TextInput::make('remarks')
                ->label('Remarks')
                ->maxLength(255),

            // Required when Principal edits past records
            Forms\Components\Textarea::make('edit_reason')
                ->label('Reason for Edit')
                ->helperText('Required when editing past attendance records.')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.admission_number')
                    ->label('Adm. No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('section.schoolClass.name')
                    ->label('Class')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('section.name')
                    ->label('Section')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        'leave'   => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Marked By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->default('—')
                    ->limit(30)
                    ->toggleable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent'  => 'Absent',
                        'late'    => 'Late',
                        'leave'   => 'Leave',
                    ]),

                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Section')
                    ->options(fn () =>
                        Section::with('schoolClass')
                            ->get()
                            ->mapWithKeys(fn ($s) => [
                                $s->id => $s->schoolClass->name . ' – ' . $s->name,
                            ])
                    ),

                Tables\Filters\SelectFilter::make('class')
                    ->label('Class')
                    ->options(fn () =>
                        SchoolClass::orderBy('numeric_order')->get()->pluck('name', 'id')
                    )
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['value'], fn ($q, $v) =>
                            $q->whereHas('section', fn ($s) =>
                                $s->where('school_class_id', $v)
                            )
                        )
                    ),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('to')->label('To'),
                    ])
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['from'], fn ($q, $v) => $q->whereDate('date', '>=', $v))
                          ->when($data['to'],   fn ($q, $v) => $q->whereDate('date', '<=', $v))
                    ),
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Correct')
                    ->modalHeading('Correct Attendance Record'),
            ])

            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendance::route('/'),
            'edit'   => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
