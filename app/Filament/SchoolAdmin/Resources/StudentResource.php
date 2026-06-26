<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\StudentResource\Pages;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model           = StudentProfile::class;
    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Students';
    protected static ?string $navigationGroup = 'People';
    protected static ?int    $navigationSort  = 1;

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

            // ── Required Information ──────────────────────────────────────
            Forms\Components\Section::make('Student Information')
                ->description('Fields marked * are required.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Student Full Name *')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('father_name')
                        ->label("Father's Name *")
                        ->required()
                        ->maxLength(100),

                    Forms\Components\Select::make('current_section_id')
                        ->label('Class & Section *')
                        ->options(function () {
                            $currentYear = AcademicYear::where('is_current', true)->first();
                            if (! $currentYear) {
                                return [];
                            }
                            return Section::with('schoolClass')
                                ->where('academic_year_id', $currentYear->id)
                                ->get()
                                ->mapWithKeys(fn ($s) => [
                                    $s->id => $s->schoolClass->name . ' – ' . $s->name
                                ]);
                        })
                        ->required()
                        ->searchable()
                        ->live() // triggers portal access section visibility
                        ->helperText('Only sections from the current academic year are shown.'),

                    Forms\Components\FileUpload::make('profile_photo')
                        ->label('Student Photo')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('3:4')
                        ->directory('student-photos')
                        ->visibility('public')
                        ->helperText('Optional. Passport size recommended.'),
                ])->columns(2),

            // ── Admission Number ──────────────────────────────────────────
            Forms\Components\Section::make('Admission Number')
                ->description('Each student gets a unique ID. You can use the auto-generated one or enter your own.')
                ->schema([
                    Forms\Components\Toggle::make('custom_admission_number')
                        ->label('Enter my own admission number')
                        ->live()
                        ->default(false)
                        ->helperText('Turn ON to type your school\'s existing numbering format.'),

                    // Auto-generate preview (read-only)
                    Forms\Components\Placeholder::make('auto_number_preview')
                        ->label('Auto-Generated Number')
                        ->content(function () {
                            $preview = StudentProfile::generateAdmissionNumber();
                            return "Your student will receive: {$preview}";
                        })
                        ->visible(fn (Forms\Get $get) => ! $get('custom_admission_number')),

                    // Manual entry
                    Forms\Components\TextInput::make('admission_number')
                        ->label('Custom Admission Number')
                        ->maxLength(30)
                        ->visible(fn (Forms\Get $get) => $get('custom_admission_number'))
                        ->required(fn (Forms\Get $get) => $get('custom_admission_number'))
                        ->helperText('Must be unique within your school.'),

                    Forms\Components\DatePicker::make('admission_date')
                        ->label('Admission Date')
                        ->default(today())
                        ->maxDate(today()),
                ])->columns(2),

            // ── Personal Details (Optional) ───────────────────────────────
            Forms\Components\Section::make('Personal Details')
                ->description('Optional — can be filled in later.')
                ->collapsed()
                ->schema([
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('Date of Birth')
                        ->maxDate(today()),

                    Forms\Components\Select::make('gender')
                        ->options(['male' => 'Male', 'female' => 'Female']),

                    Forms\Components\Select::make('blood_group')
                        ->label('Blood Group')
                        ->options([
                            'A+'  => 'A+', 'A-'  => 'A-',
                            'B+'  => 'B+', 'B-'  => 'B-',
                            'O+'  => 'O+', 'O-'  => 'O-',
                            'AB+' => 'AB+', 'AB-' => 'AB-',
                        ]),

                    Forms\Components\TextInput::make('b_form_number')
                        ->label('B-Form Number (Child CNIC)')
                        ->placeholder('00000-0000000-0')
                        ->maxLength(15),

                    Forms\Components\TextInput::make('roll_number')
                        ->label('Roll Number')
                        ->helperText('Class roll number for exams. Can be assigned later.')
                        ->maxLength(20),
                ])->columns(2),

            // ── Address ───────────────────────────────────────────────────
            Forms\Components\Section::make('Address')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('address')
                        ->label('Street Address')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('city')
                        ->label('City')
                        ->maxLength(100),
                ])->columns(2),

            // ── Portal Access (only for Grade 6+) ─────────────────────────
            Forms\Components\Section::make('Student Portal Access')
                ->description('Students in Grade 6 and above can log into the student portal.')
                ->schema([
                    Forms\Components\Placeholder::make('login_info')
                        ->label('')
                        ->content(function (Forms\Get $get) {
                            $sectionId = $get('current_section_id');
                            if (! $sectionId) {
                                return 'Select a class first to see portal access details.';
                            }
                            $section = Section::with('schoolClass')->find($sectionId);
                            $level   = $section?->schoolClass?->level;
                            if (in_array($level, ['middle', 'secondary', 'higher_secondary'])) {
                                return '✅ This student will receive portal login credentials. You can provide an email below, or one will be auto-generated.';
                            }
                            return 'ℹ️ This class level does not require student login. Only parent portal access applies.';
                        }),

                    Forms\Components\TextInput::make('email')
                        ->label('Student Email (optional)')
                        ->email()
                        ->unique('users', 'email')
                        ->helperText('Leave blank to auto-generate a login email.')
                        ->visible(function (Forms\Get $get) {
                            $sectionId = $get('current_section_id');
                            if (! $sectionId) return false;
                            $section = Section::with('schoolClass')->find($sectionId);
                            return in_array(
                                $section?->schoolClass?->level,
                                ['middle', 'secondary', 'higher_secondary']
                            );
                        }),
                ]),

        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Student photo or auto-avatar
                Tables\Columns\ImageColumn::make('user.profile_photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (StudentProfile $r) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($r->user->name ?? 'S') .
                        '&color=fff&background=059669&size=64'
                    ),

                Tables\Columns\TextColumn::make('admission_number')
                    ->label('Adm. No.')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('father_name')
                    ->label("Father's Name")
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('section.schoolClass.name')
                    ->label('Class')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('section.name')
                    ->label('Section')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'      => 'success',
                        'graduated'   => 'info',
                        'transferred' => 'warning',
                        'withdrawn'   => 'danger',
                        default       => 'gray',
                    }),

                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Login')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (StudentProfile $r) =>
                        $r->user?->is_active ? 'Portal login active' : 'No portal login'
                    ),

                Tables\Columns\TextColumn::make('admission_date')
                    ->label('Admitted')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('section_class')
                    ->label('Class')
                    ->options(fn () =>
                        SchoolClass::orderBy('numeric_order')
                            ->get()
                            ->pluck('name', 'id')
                    )
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'], fn ($q, $v) =>
                            $q->whereHas('section', fn ($s) =>
                                $s->where('school_class_id', $v)
                            )
                        )
                    ),

                Tables\Filters\SelectFilter::make('current_section_id')
                    ->label('Section')
                    ->options(fn () => Section::with('schoolClass')
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => $s->schoolClass->name . ' – ' . $s->name
                        ])
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'      => 'Active',
                        'graduated'   => 'Graduated',
                        'transferred' => 'Transferred',
                        'withdrawn'   => 'Withdrawn',
                    ]),

            
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // ── Link Parent ──────────────────────────────────────────
                Tables\Actions\Action::make('link_parent')
                    ->label('Add Parent')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('relationship')
                            ->options([
                                'Father'   => 'Father',
                                'Mother'   => 'Mother',
                                'Guardian' => 'Guardian',
                                'Brother'  => 'Brother',
                                'Sister'   => 'Sister',
                                'Other'    => 'Other',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('parent_name')
                            ->label("Parent's Full Name")
                            ->required(),

                        Forms\Components\TextInput::make('parent_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required(),

                        Forms\Components\TextInput::make('parent_email')
                            ->label('Email (for portal login)')
                            ->email()
                            ->helperText('Optional. Required if parent wants portal access.'),

                        Forms\Components\TextInput::make('parent_cnic')
                            ->label('CNIC')
                            ->placeholder('00000-0000000-0')
                            ->maxLength(15),

                        Forms\Components\Toggle::make('is_primary_contact')
                            ->label('Primary Contact')
                            ->default(true)
                            ->helperText('Primary contact receives all SMS and fee notifications.'),
                    ])
                    ->modalHeading(fn (StudentProfile $r) => 'Add Parent for ' . ($r->user->name ?? 'Student'))
                    ->action(function (StudentProfile $record, array $data): void {
                        $tempPassword = \Illuminate\Support\Str::random(10);

                        $email = ! empty($data['parent_email'])
                            ? $data['parent_email']
                            : 'parent.' . \Illuminate\Support\Str::slug($data['parent_phone']) . '@' . tenant()->getSchool()->subdomain . '.local';

                        // Create parent User account
                        $parentUser = \App\Models\User::create([
                            'school_id' => tenant()->getSchoolId(),
                            'name'      => $data['parent_name'],
                            'email'     => $email,
                            'phone'     => $data['parent_phone'],
                            'password'  => $tempPassword,
                            'role'      => 'parent',
                            'is_active' => ! empty($data['parent_email']), // active only if real email given
                        ]);

                        // Create parent profile
                        $parentProfile = \App\Models\ParentProfile::create([
                            'school_id'    => tenant()->getSchoolId(),
                            'user_id'      => $parentUser->id,
                            'cnic'         => $data['parent_cnic'] ?? null,
                            'relationship' => $data['relationship'],
                        ]);

                        // Link parent to student
                        $record->parents()->attach($parentProfile->id, [
                            'school_id'          => tenant()->getSchoolId(),
                            'is_primary_contact' => $data['is_primary_contact'],
                        ]);

                        $msg = "Parent created: {$data['parent_name']} ({$data['relationship']})";
                        if (! empty($data['parent_email'])) {
                            $msg .= "\nEmail: {$email} | Password: {$tempPassword}";
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Parent linked successfully!')
                            ->body($msg)
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // ── Activate Student Login ────────────────────────────────
                Tables\Actions\Action::make('activate_login')
                    ->label('Activate Login')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (StudentProfile $r) =>
                        ! $r->user?->is_active &&
                        $r->requiresStudentLogin()
                    )
                    ->action(function (StudentProfile $record): void {
                        $tempPassword = \Illuminate\Support\Str::random(10);
                        $record->user->update([
                            'is_active' => true,
                            'password'  => $tempPassword,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Student login activated!')
                            ->body("Email: {$record->user->email} | Password: {$tempPassword}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Mark selected students as transferred
                    Tables\Actions\BulkAction::make('mark_transferred')
                        ->label('Mark as Transferred')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'transferred'])),

                    Tables\Actions\BulkAction::make('mark_withdrawn')
                        ->label('Mark as Withdrawn')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'withdrawn'])),
                ]),
            ])

            ->defaultSort('admission_number');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view'   => Pages\ViewStudent::route('/{record}'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
