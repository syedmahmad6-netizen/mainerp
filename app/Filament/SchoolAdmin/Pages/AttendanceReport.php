<?php
namespace App\Filament\SchoolAdmin\Pages;

use App\Models\Attendance;
use App\Models\AttendanceSettings;
use App\Models\Section;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class AttendanceReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Attendance Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.school-admin.pages.attendance-report';

    public ?array $data      = [];
    public array  $report    = [];
    public int    $threshold = 75;

    public function mount(): void
    {
        $this->threshold = AttendanceSettings::forCurrentSchool()->low_threshold_percent;
        $this->form->fill([
            'section_id'  => null,
            'month'       => now()->format('Y-m'),
            'date'        => today()->format('Y-m-d'),
            'report_type' => 'monthly_summary',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('report_type')
                ->label('Report Type')
                ->options(['monthly_summary' => 'Monthly Summary per Student', 'low_attendance' => 'Low Attendance List', 'daily' => 'Daily Attendance'])
                ->required()
                ->default('monthly_summary'),

            Forms\Components\Select::make('section_id')
                ->label('Class & Section')
                ->options(fn () => Section::with('schoolClass')->get()->mapWithKeys(fn ($s) => [$s->id => $s->schoolClass->name . ' – ' . $s->name]))
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('month')
                ->label('Month (used for Summary / Low Attendance reports)')
                ->type('month')
                ->default(now()->format('Y-m')),

            Forms\Components\DatePicker::make('date')
                ->label('Date (used for Daily report)')
                ->default(today()),
        ])->statePath('data')->columns(2);
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        if (empty($data['section_id'])) return;

        $students = StudentProfile::where('current_section_id', $data['section_id'])->where('status', 'active')->with('user')->get();

        if ($data['report_type'] === 'daily') {
            $date = $data['date'] ?? today()->format('Y-m-d');
            $this->report = $students->map(function ($student) use ($date) {
                $rec = Attendance::where('student_id', $student->id)->whereDate('date', $date)->first();
                return ['name' => $student->user->name ?? '—', 'admission_no' => $student->admission_number ?? '—',
                    'status' => $rec?->status ?? 'not_marked', 'remarks' => $rec?->remarks ?? '—'];
            })->toArray();
        } else {
            $month = Carbon::parse(($data['month'] ?? now()->format('Y-m')) . '-01');
            $from  = $month->startOfMonth()->format('Y-m-d');
            $to    = $month->copy()->endOfMonth()->format('Y-m-d');

            $allStudents = $students->map(function ($student) use ($from, $to) {
                $summary = Attendance::getSummary($student->id, $from, $to);
                $pct     = Attendance::getPercentage($student->id, $from, $to);
                return ['name' => $student->user->name ?? '—', 'admission_no' => $student->admission_number ?? '—',
                    'present' => $summary['present'], 'absent' => $summary['absent'], 'late' => $summary['late'],
                    'leave' => $summary['leave'], 'total' => $summary['total'], 'percentage' => $pct,
                    'status' => $pct >= 75 ? 'good' : 'low'];
            });

            if ($data['report_type'] === 'low_attendance') {
                $this->report = $allStudents->filter(fn ($s) => $s['status'] === 'low')->values()->toArray();
            } else {
                $this->report = $allStudents->toArray();
            }
        }
    }

    protected function getFormActions(): array
    {
        return [Action::make('generate')->label('Generate Report')->submit('generate')->icon('heroicon-o-magnifying-glass')];
    }
}