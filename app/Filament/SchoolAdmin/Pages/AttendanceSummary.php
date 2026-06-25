<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSettings;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class AttendanceSummary extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Attendance Summary';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.school-admin.pages.attendance-summary';

    public ?array $data      = [];
    public array  $summary   = [];
    public int    $threshold = 75;

    public function mount(): void
    {
        $this->threshold = AttendanceSettings::forCurrentSchool()->low_threshold_percent;

        $this->form->fill([
            'section_id' => null,
            'month'      => today()->format('Y-m'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
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
                    ->searchable(),

                Forms\Components\TextInput::make('month')
                    ->label('Month')
                    ->type('month')    // HTML month picker (YYYY-MM)
                    ->required(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    /**
     * Generate the attendance summary for the selected section and month.
     */
    public function generate(): void
    {
        $data = $this->form->getState();

        if (empty($data['section_id']) || empty($data['month'])) {
            return;
        }

        $month     = Carbon::parse($data['month'] . '-01');
        $from      = $month->startOfMonth()->format('Y-m-d');
        $to        = $month->copy()->endOfMonth()->format('Y-m-d');

        $students  = StudentProfile::where('current_section_id', $data['section_id'])
            ->where('status', 'active')
            ->with('user')
            ->get();

        $this->summary = $students->map(function ($student) use ($from, $to) {
            $summary    = Attendance::getSummary($student->id, $from, $to);
            $percentage = Attendance::getPercentage($student->id, $from, $to);

            return [
                'student_id'   => $student->id,
                'name'         => $student->user->name ?? '—',
                'admission_no' => $student->admission_number ?? '—',
                'present'      => $summary['present'],
                'absent'       => $summary['absent'],
                'late'         => $summary['late'],
                'leave'        => $summary['leave'],
                'total'        => $summary['total'],
                'percentage'   => $percentage,
                'status'       => $percentage === 0 ? 'no_data'
                    : ($percentage >= $this->threshold ? 'good' : 'low'),
            ];
        })->sortBy('name')->values()->toArray();
    }
}
