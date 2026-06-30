<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\ExamResource\Pages;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ExamResource extends Resource {
    protected static ?string $model           = Exam::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Exams';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 8;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Exam Details')->schema([
                Forms\Components\TextInput::make('name')->label('Exam Name')->placeholder('e.g. First Monthly Test 2025, Mid Term Exam')->required()->maxLength(100),
                Forms\Components\Select::make('exam_type_id')->label('Exam Type')->options(fn()=>ExamType::all()->pluck('name','id'))->required()->searchable(),
                Forms\Components\Select::make('academic_year_id')->label('Academic Year')->options(fn()=>AcademicYear::all()->pluck('name','id'))->default(fn()=>AcademicYear::where('is_current',true)->value('id'))->required(),
                Forms\Components\Select::make('status')->label('Status')->options(['draft'=>'Draft (marks being entered)','active'=>'Active (exam in progress)','published'=>'Published (students can view)'])->default('draft')->required(),
                Forms\Components\DatePicker::make('start_date')->label('Start Date'),
                Forms\Components\DatePicker::make('end_date')->label('End Date')->after('start_date'),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Exam Name')->sortable()->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('examType.name')->label('Type')->badge()->color('info'),
            Tables\Columns\TextColumn::make('academicYear.name')->label('Year')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('start_date')->label('From')->date('d M Y')->sortable(),
            Tables\Columns\TextColumn::make('end_date')->label('To')->date('d M Y'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state)=>match($state){'draft'=>'gray','active'=>'warning','published'=>'success',default=>'gray'}),
            Tables\Columns\TextColumn::make('results_count')->label('Results')->counts('results')->badge()->color('primary'),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(['draft'=>'Draft','active'=>'Active','published'=>'Published']),
            Tables\Filters\SelectFilter::make('academic_year_id')->label('Year')->options(fn()=>AcademicYear::all()->pluck('name','id')),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('publish')
                ->label('Publish Results')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Publishing will make results visible to students and parents. This cannot be undone.')
                ->visible(fn(Exam $record)=>$record->status!=='published')
                ->action(function(Exam $record): void {
                    $record->update(['status'=>'published']);
                    Notification::make()->title('Results published! Students and parents can now view them.')->success()->send();
                }),
            Tables\Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn(Exam $record)=>$record->status==='published')
                ->action(fn(Exam $record)=>$record->update(['status'=>'draft'])),
        ])->defaultSort('created_at','desc');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListExams::route('/'),'create'=>Pages\CreateExam::route('/create'),'edit'=>Pages\EditExam::route('/{record}/edit')];
    }
}