<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class StudentIdCards extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'ID Cards';
    protected static ?string $navigationGroup = 'People';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.school-admin.pages.student-id-cards';

    public ?array $data     = [];
    public array  $students = [];

    public function mount(): void
    {
        $this->form->fill([
            'generate_for'      => 'section',
            'section_id'        => null,
            'academic_year_id'  => AcademicYear::where('is_current', true)->value('id'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Radio::make('generate_for')
                ->label('Generate For')
                ->options(['section' => 'One Class/Section', 'all' => 'All Active Students'])
                ->default('section')
                ->live()
                ->inline(),

            Forms\Components\Select::make('section_id')
                ->label('Class & Section')
                ->options(fn () =>
                    Section::with('schoolClass')->get()
                        ->mapWithKeys(fn ($s) => [$s->id => $s->schoolClass->name . ' – ' . $s->name])
                )
                ->searchable(),

            Forms\Components\Select::make('academic_year_id')
                ->label('Academic Year')
                ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                ->default(fn () => AcademicYear::where('is_current', true)->value('id')),
        ])->statePath('data')->columns(3);
    }

    public function generate(): void
    {
        $data  = $this->form->getState();
        $query = StudentProfile::with(['user', 'section.schoolClass', 'section.academicYear', 'parents.user'])
            ->where('status', 'active');

        if ($data['generate_for'] === 'section' && ! empty($data['section_id'])) {
            $query->where('current_section_id', $data['section_id']);
        }

        $this->students = $query->orderBy('id')->get()->toArray();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate ID Cards')
                ->submit('generate')
                ->icon('heroicon-o-identification'),
        ];
    }
}