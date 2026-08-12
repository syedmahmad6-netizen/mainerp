<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\TeacherResource\Pages;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model           = TeacherProfile::class;
    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Teachers';
    protected static ?string $navigationGroup = 'People';
    protected static ?int    $navigationSort  = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    // ─────────────────────────────────────────────────────────────────────
    // FORM
    // ─────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Personal Information ──────────────────────────────────────
            Forms\Components\Section::make('Personal Information')
                ->schema([
                    Forms\Components\FileUpload::make('profile_photo')
                        ->label('Photo')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->directory('teacher-photos')
                        ->visibility('public')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('name')
                        ->label('Full Name *')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('cnic')
                        ->label('CNIC *')
                        ->required()
                        ->placeholder('00000-0000000-0')
                        ->maxLength(15)
                        ->helperText('Pakistani National ID — must be unique per school.'),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number *')
                        ->tel()
                        ->required(),

                    
                    
                        Forms\Components\TextInput::make('email')
                        ->label('Email (optional)')
                        ->email()
                        ->unique(table: 'users', column: 'email')
                        ->helperText('Leave blank to auto-generate a portal login email.'),
                    
                        Forms\Components\DatePicker::make('date_of_birth')
                        ->label('Date of Birth')
                        ->maxDate(today()->subYears(18)),

                    Forms\Components\Select::make('gender')
                        ->options(['male' => 'Male', 'female' => 'Female']),
                ])->columns(2),

            // ── Employment Details ────────────────────────────────────────
            Forms\Components\Section::make('Employment Details')
                ->schema([
                    Forms\Components\Toggle::make('custom_employee_id')
                        ->label('Enter custom Employee ID')
                        ->live()
                        ->default(false),

                    Forms\Components\Placeholder::make('auto_employee_id_preview')
                        ->label('Auto-Generated Employee ID')
                        ->content(fn () => 'Will be assigned: ' . TeacherProfile::generateEmployeeId())
                        ->visible(fn (Forms\Get $get) => ! $get('custom_employee_id')),

                    Forms\Components\TextInput::make('employee_id')
                        ->label('Custom Employee ID')
                        ->maxLength(30)
                        ->visible(fn (Forms\Get $get) => $get('custom_employee_id'))
                        ->required(fn (Forms\Get $get) => $get('custom_employee_id')),

                    Forms\Components\DatePicker::make('joining_date')
                        ->label('Date of Joining')
                        ->default(today()),

                    Forms\Components\Select::make('employment_type')
                        ->label('Employment Type')
                        ->options([
                            'permanent'  => 'Permanent',
                            'contract'   => 'Contract',
                            'part_time'  => 'Part-Time',
                            'visiting'   => 'Visiting',
                        ])
                        ->default('permanent')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active'      => 'Active',
                            'on_leave'    => 'On Leave',
                            'resigned'    => 'Resigned',
                            'terminated'  => 'Terminated',
                        ])
                        ->default('active')
                        ->required(),
                ])->columns(2),

            // ── Academic Qualifications ───────────────────────────────────
            Forms\Components\Section::make('Academic Qualifications')
                ->schema([
                    Forms\Components\TextInput::make('qualification')
                        ->label('Highest Qualification')
                        ->placeholder('e.g. B.Ed, M.Ed, MA English, MSc Maths')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('specialization')
                        ->label('Specialization / Department')
                        ->placeholder('e.g. Mathematics, English, Science')
                        ->maxLength(100),

                    Forms\Components\CheckboxList::make('subjects')
                        ->label('Subjects This Teacher Can Teach')
                        ->options(fn () => Subject::all()->pluck('name', 'id'))
                        ->columns(3)
                        ->helperText('Select all subjects this teacher is qualified to teach. Used in timetable assignment.')
                        ->columnSpanFull(),
                ])->columns(2),

            // ── Salary (Confidential) ─────────────────────────────────────
            Forms\Components\Section::make('Salary Information')
                ->description('This information is confidential and only visible to Principal/Manager.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('salary')
                        ->label('Monthly Salary (PKR)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->minValue(0),
                ])->columns(2),

        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.profile_photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (TeacherProfile $r) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($r->user->name ?? 'T') .
                        '&color=fff&background=7c3aed&size=64'
                    ),

                Tables\Columns\TextColumn::make('employee_id')
                    ->label('Emp. ID')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('qualification')
                    ->label('Qualification')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Specialization')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'permanent' => 'success',
                        'contract'  => 'warning',
                        'part_time' => 'info',
                        'visiting'  => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'     => 'success',
                        'on_leave'   => 'warning',
                        'resigned'   => 'danger',
                        'terminated' => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subjects_count')
                    ->label('Subjects')
                    ->counts('subjects')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Portal')
                    ->boolean()
                    ->tooltip('Portal login status'),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract'  => 'Contract',
                        'part_time' => 'Part-Time',
                        'visiting'  => 'Visiting',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'     => 'Active',
                        'on_leave'   => 'On Leave',
                        'resigned'   => 'Resigned',
                        'terminated' => 'Terminated',
                    ]),

                Tables\Filters\SelectFilter::make('gender')
                    ->query(fn ($query, $data) =>
                        $query->when($data['value'], fn ($q, $v) =>
                            $q->where('gender', $v)
                        )
                    )
                    ->options(['male' => 'Male', 'female' => 'Female']),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // ── Quick status change ───────────────────────────────────
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (TeacherProfile $r) =>
                        $r->status === 'active' ? 'Mark on Leave' : 'Mark Active'
                    )
                    ->icon(fn (TeacherProfile $r) =>
                        $r->status === 'active'
                        ? 'heroicon-o-pause-circle'
                        : 'heroicon-o-play-circle'
                    )
                    ->color(fn (TeacherProfile $r) =>
                        $r->status === 'active' ? 'warning' : 'success'
                    )
                    ->requiresConfirmation()
                    ->action(fn (TeacherProfile $r) => $r->update([
                        'status' => $r->status === 'active' ? 'on_leave' : 'active',
                    ])),

                // ── Reset portal password ─────────────────────────────────
                Tables\Actions\Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('A new random password will be generated. Note it down before closing.')
                    ->action(function (TeacherProfile $record): void {
                        $newPassword = \Illuminate\Support\Str::random(10);
                        $record->user->update(['password' => $newPassword]);

                        \Filament\Notifications\Notification::make()
                            ->title('Password reset for ' . $record->user->name)
                            ->body("New Password: {$newPassword}\n⚠️ Share this with the teacher securely.")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_on_leave')
                        ->label('Mark as On Leave')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'on_leave'])),

                    Tables\Actions\BulkAction::make('mark_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'active'])),
                ]),
            ])

            ->defaultSort('employee_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view'   => Pages\ViewTeacher::route('/{record}'),
            'edit'   => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
