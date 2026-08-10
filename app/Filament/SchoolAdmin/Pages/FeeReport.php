<?php
namespace App\Filament\SchoolAdmin\Pages;

use App\Models\AcademicYear;
use App\Models\FeeCollection;
use App\Models\Section;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class FeeReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Fee Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.school-admin.pages.fee-report';

    public ?array $data    = [];
    public array  $report  = [];
    public array  $summary = [];

    public function mount(): void
    {
        $this->form->fill([
            'report_type'      => 'monthly',
            'month'            => now()->format('Y-m'),
            'academic_year_id' => AcademicYear::where('is_current', true)->value('id'),
            'section_id'       => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('report_type')
                ->label('Report Type')
                ->options(['monthly' => 'Monthly Collection', 'outstanding' => 'Outstanding Fees', 'classwise' => 'Class-Wise Summary'])
                ->required()
                ->default('monthly'),

            Forms\Components\TextInput::make('month')
                ->label('Month (used for Monthly Collection report)')
                ->type('month')
                ->default(now()->format('Y-m')),

            Forms\Components\Select::make('academic_year_id')
                ->label('Academic Year')
                ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                ->default(fn () => AcademicYear::where('is_current', true)->value('id')),

            Forms\Components\Select::make('section_id')
                ->label('Filter by Section (optional)')
                ->options(fn () => Section::with('schoolClass')->get()->mapWithKeys(fn ($s) => [$s->id => $s->schoolClass->name . ' – ' . $s->name]))
                ->nullable()
                ->placeholder('All Sections')
                ->searchable(),
        ])->statePath('data')->columns(2);
    }

    public function generate(): void
    {
        $data  = $this->form->getState();
        $query = FeeCollection::with(['student.user', 'student.section.schoolClass', 'feeType']);

        if ($data['academic_year_id']) $query->where('academic_year_id', $data['academic_year_id']);
        if ($data['section_id']) {
            $query->whereHas('student', fn ($q) => $q->where('current_section_id', $data['section_id']));
        }

        if ($data['report_type'] === 'monthly') {
            $month = Carbon::parse($data['month'] . '-01');
            $query->whereYear('fee_month', $month->year)->whereMonth('fee_month', $month->month);
        } elseif ($data['report_type'] === 'outstanding') {
            $query->whereIn('status', ['pending', 'overdue', 'partial']);
        }

        $records = $query->orderBy('fee_month', 'desc')->get();

        $this->report = $records->map(fn ($r) => [
            'student'      => $r->student?->user?->name ?? '—',
            'admission_no' => $r->student?->admission_number ?? '—',
            'class'        => $r->student?->section?->schoolClass?->name ?? '—',
            'section'      => $r->student?->section?->name ?? '—',
            'fee_type'     => $r->feeType?->name ?? '—',
            'month'        => $r->fee_month?->format('M Y') ?? '—',
            'amount_due'   => $r->amount_due,
            'discount'     => $r->discount,
            'amount_paid'  => $r->amount_paid,
            'balance'      => $r->balance,
            'status'       => $r->status,
            'receipt_no'   => $r->receipt_no ?? '—',
            'paid_date'    => $r->paid_date?->format('d M Y') ?? '—',
        ])->toArray();

        $this->summary = [
            'total_due'       => $records->sum('amount_due'),
            'total_discount'  => $records->sum('discount'),
            'total_collected' => $records->sum('amount_paid'),
            'total_balance'   => $records->sum('balance'),
            'paid_count'      => $records->where('status', 'paid')->count(),
            'pending_count'   => $records->whereIn('status', ['pending', 'overdue'])->count(),
            'total_records'   => $records->count(),
        ];
    }

    protected function getFormActions(): array
    {
        return [Action::make('generate')->label('Generate Report')->submit('generate')->icon('heroicon-o-magnifying-glass')];
    }
}