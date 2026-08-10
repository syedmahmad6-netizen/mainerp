<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MarkAttendance extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Mark Attendance';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.school-admin.pages.mark-attendance';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date'       => today()->format('Y-m-d'),
            'section_id' => null,
            'attendance' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Step 1: Select section and date ───────────────────────
                Forms\Components\Section::make('Select Class & Date')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->label('Class & Section')
                            ->options(function () {
                                $year = AcademicYear::where('is_current', true)->first();
                                if (! $year) return [];

                                return Section::with('schoolClass')
                                    ->where('academic_year_id', $year->id)
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => $s->schoolClass->name . ' – ' . $s->name,
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, $state) =>
                                $this->loadStudents($set, $state, $this->data['date'] ?? today()->format('Y-m-d'))
                            ),

                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->default(today())
                            ->maxDate(today()) // Cannot mark future attendance
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, $state) =>
                                $this->loadStudents($set, $this->data['section_id'] ?? null, $state)
                            ),

                        // Quick "Mark All Present" toggle
                        Forms\Components\Toggle::make('all_present')
                            ->label('Mark all as Present')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $attendance = $this->data['attendance'] ?? [];
                                foreach ($attendance as $i => $row) {
                                    $attendance[$i]['status'] = $state ? 'present' : 'absent';
                                }
                                $set('attendance', $attendance);
                            })
                            ->helperText('Toggle ON to mark everyone present, then change individuals.'),
                    ])->columns(3),

                // ── Step 2: Student list with status buttons ───────────────
                Forms\Components\Section::make('Students')
                    ->schema([
                        Forms\Components\Repeater::make('attendance')
                            ->label('')
                            ->schema([
                                // Student name — read only display
                                Forms\Components\Placeholder::make('student_name')
                                    ->label('Student')
                                    ->content(fn (Forms\Get $get) => $get('student_name') ?? '—'),

                                // Status toggle buttons — P / A / L / Leave
                                Forms\Components\ToggleButtons::make('status')
                                    ->label('Status')
                                    ->options([
                                        'present' => 'P',
                                        'absent'  => 'A',
                                        'late'    => 'Late',
                                        'leave'   => 'Leave',
                                    ])
                                    ->colors([
                                        'present' => 'success',
                                        'absent'  => 'danger',
                                        'late'    => 'warning',
                                        'leave'   => 'info',
                                    ])
                                    ->icons([
                                        'present' => 'heroicon-o-check-circle',
                                        'absent'  => 'heroicon-o-x-circle',
                                        'late'    => 'heroicon-o-clock',
                                        'leave'   => 'heroicon-o-arrow-right-on-rectangle',
                                    ])
                                    ->default('present')
                                    ->inline()
                                    ->grouped(),

                                // Hidden fields carrying IDs
                                Forms\Components\Hidden::make('student_id'),
                                Forms\Components\Hidden::make('student_name'),

                                // Optional remarks (shown on hover / optional field)
                                Forms\Components\TextInput::make('remarks')
                                    ->label('Remarks')
                                    ->placeholder('Optional note...')
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->itemLabel(fn (array $state) => $state['student_name'] ?? 'Student')
                            ->defaultItems(0)
                            ->visible(fn (Forms\Get $get) => ! empty($get('section_id')))
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Load students for the selected section and pre-fill
     * existing attendance if it has already been marked today.
     */
    private function loadStudents(Forms\Set $set, ?int $sectionId, ?string $date): void
    {
        if (! $sectionId || ! $date) {
            $set('attendance', []);
            return;
        }

        $students = StudentProfile::where('current_section_id', $sectionId)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('id')
            ->get();

        // Load any existing attendance records for this date
        $existing = Attendance::where('section_id', $sectionId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(fn ($student) => [
            'student_id'   => $student->id,
            'student_name' => $student->user->name ?? 'Unknown',
            'status'       => $existing[$student->id]?->status ?? 'present',
            'remarks'      => $existing[$student->id]?->remarks ?? '',
        ])->values()->toArray();

        $set('attendance', $rows);
    }

    /**
     * Save all attendance records for the selected section and date.
     */
    public function save(): void
    {
        $data = $this->form->getState();

        if (empty($data['section_id']) || empty($data['attendance'])) {
            Notification::make()
                ->title('Please select a class and load students first.')
                ->warning()
                ->send();
            return;
        }

        $date      = Carbon::parse($data['date']);
        $isToday   = $date->isToday();
        $isPast    = $date->isPast() && ! $isToday;
        $user      = auth()->user();

        // Teachers can only mark today's attendance
        if ($isPast && $user->role === 'teacher') {
            Notification::make()
                ->title('Teachers can only mark today\'s attendance.')
                ->body('Contact the principal to edit past attendance.')
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () use ($data, $date) {
            foreach ($data['attendance'] as $row) {
                Attendance::updateOrCreate(
                    [
                        'school_id'  => tenant()->getSchoolId(),
                        'student_id' => $row['student_id'],
                        'section_id' => $data['section_id'],
                        'date'       => $date->format('Y-m-d'),
                    ],
                    [
                        'teacher_id' => auth()->id(),
                        'status'     => $row['status'],
                        'remarks'    => $row['remarks'] ?? null,
                        'edited_by'  => auth()->id(),
                    ]
                );
            }
        });

        $total   = count($data['attendance']);
        $absent  = collect($data['attendance'])->where('status', 'absent')->count();
        $present = $total - $absent;

        Notification::make()
            ->title('Attendance saved!')
            ->body("{$present} Present · {$absent} Absent · {$total} Total")
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Attendance')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }
}
