<?php
namespace App\Filament\SchoolAdmin\Pages;

use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class StudentReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Student Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.school-admin.pages.student-report';

    public ?array $data       = [];
    public array  $report     = [];
    public int    $totalCount = 0;

    public function mount(): void
    {
        $this->form->fill(['report_type' => 'all_active', 'section_id' => null, 'status' => 'active']);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('report_type')
                ->label('Report Type')
                ->options(['all_active' => 'All Active Students', 'by_section' => 'Students by Section', 'by_status' => 'Students by Status'])
                ->required()
                ->default('all_active'),

            Forms\Components\Select::make('section_id')
                ->label('Class & Section (used for "Students by Section")')
                ->options(fn () => Section::with('schoolClass')->get()->mapWithKeys(fn ($s) => [$s->id => $s->schoolClass->name . ' – ' . $s->name]))
                ->searchable()
                ->nullable(),

            Forms\Components\Select::make('status')
                ->label('Status (used for "Students by Status")')
                ->options(['active' => 'Active', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'withdrawn' => 'Withdrawn'])
                ->default('active'),
        ])->statePath('data')->columns(2);
    }

    public function generate(): void
    {
        $data  = $this->form->getState();
        $query = StudentProfile::with(['user', 'section.schoolClass', 'parents.user']);

        switch ($data['report_type']) {
            case 'all_active': $query->where('status', 'active'); break;
            case 'by_section': if ($data['section_id']) $query->where('current_section_id', $data['section_id']); break;
            case 'by_status':  if ($data['status']) $query->where('status', $data['status']); break;
        }

        $students = $query->orderBy('id')->get();
        $this->totalCount = $students->count();
        $this->report = $students->map(fn ($s) => [
            'name'           => $s->user?->name ?? '—',
            'admission_no'   => $s->admission_number ?? '—',
            'father_name'    => $s->father_name ?? '—',
            'class'          => $s->section?->schoolClass?->name ?? '—',
            'section'        => $s->section?->name ?? '—',
            'gender'         => ucfirst($s->gender ?? '—'),
            'status'         => ucfirst($s->status),
            'phone'          => $s->user?->phone ?? '—',
            'parent_phone'   => $s->parents->first()?->user?->phone ?? '—',
            'admission_date' => $s->admission_date?->format('d M Y') ?? '—',
        ])->toArray();
    }

    protected function getFormActions(): array
    {
        return [Action::make('generate')->label('Generate Report')->submit('generate')->icon('heroicon-o-magnifying-glass')];
    }
}