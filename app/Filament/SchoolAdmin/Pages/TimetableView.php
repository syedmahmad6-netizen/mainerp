<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\TimeSlot;
use App\Models\Timetable;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class TimetableView extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'View Timetable';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.school-admin.pages.timetable-view';

    // 'class' or 'teacher'
    public string $viewMode = 'class';

    public ?array $data      = [];
    public array  $timeSlots = [];
    public array  $grid      = [];
    public array  $days      = [];
    public string $viewTitle = '';

    public function mount(): void
    {
        // Determine if school uses Mon–Sat or Mon–Fri
        $settings  = tenant()->getSchool()->settings ?? [];
        $satEnabled = ($settings['working_days'] ?? 'mon_fri') === 'mon_sat';
        $this->days = Timetable::allDays($satEnabled);

        $this->form->fill([
            'view_mode'       => 'class',
            'section_id'      => null,
            'teacher_id'      => null,
            'academic_year_id'=> AcademicYear::where('is_current', true)->value('id'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('view_mode')
                    ->label('View By')
                    ->options([
                        'class'   => '🏫 Class Timetable',
                        'teacher' => '👤 Teacher Timetable',
                    ])
                    ->default('class')
                    ->inline()
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\Select::make('section_id')
                    ->label('Class & Section')
                    ->options(fn () =>
                        Section::with('schoolClass')
                            ->get()
                            ->mapWithKeys(fn ($s) => [
                                $s->id => $s->schoolClass->name . ' – ' . $s->name,
                            ])
                    )
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('view_mode') === 'class'),

                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(fn () =>
                        User::where('role', 'teacher')
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('view_mode') === 'teacher'),

                Forms\Components\Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                    ->default(fn () => AcademicYear::where('is_current', true)->value('id'))
                    ->required(),
            ])
            ->statePath('data')
            ->columns(3);
    }

    /**
     * Load and build the timetable grid.
     */
    public function loadTimetable(): void
    {
        $data      = $this->form->getState();
        $yearId    = $data['academic_year_id'];
        $viewMode  = $data['view_mode'] ?? 'class';

        // Load all non-break time slots
        $this->timeSlots = TimeSlot::orderBy('slot_order')->get()->toArray();

        if ($viewMode === 'class' && ! empty($data['section_id'])) {

            $section         = Section::with('schoolClass')->find($data['section_id']);
            $this->viewTitle = $section
                ? $section->schoolClass->name . ' – ' . $section->name . ' Timetable'
                : 'Class Timetable';

            $entries = Timetable::where('section_id', $data['section_id'])
                ->where('academic_year_id', $yearId)
                ->with(['subject', 'teacher', 'timeSlot'])
                ->get();

        } elseif ($viewMode === 'teacher' && ! empty($data['teacher_id'])) {

            $teacher         = User::find($data['teacher_id']);
            $this->viewTitle = ($teacher->name ?? 'Teacher') . '\'s Timetable';

            $entries = Timetable::where('teacher_id', $data['teacher_id'])
                ->where('academic_year_id', $yearId)
                ->with(['subject', 'teacher', 'timeSlot', 'section.schoolClass'])
                ->get();

        } else {
            $this->grid = [];
            return;
        }

        // Build 2D grid: $grid[$dayOfWeek][$timeSlotId] = entry
        $this->grid = [];
        foreach ($entries as $entry) {
            $this->grid[$entry->day_of_week][$entry->time_slot_id] = $entry;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('load')
                ->label('Show Timetable')
                ->submit('loadTimetable')
                ->icon('heroicon-o-eye'),
        ];
    }
}
