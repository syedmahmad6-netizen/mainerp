<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\ExamScheduleResource\Pages;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ExamScheduleResource extends Resource {
    protected static ?string $model           = ExamSchedule::class;
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Exam Schedule';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 9;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('exam_id')->label('Exam')->options(fn()=>Exam::all()->pluck('name','id'))->required()->searchable(),
            Forms\Components\Select::make('school_class_id')->label('Class')->options(fn()=>SchoolClass::orderBy('numeric_order')->get()->pluck('name','id'))->required()->searchable(),
            Forms\Components\Select::make('subject_id')->label('Subject')->options(fn()=>Subject::all()->pluck('name','id'))->required()->searchable(),
            Forms\Components\DatePicker::make('exam_date')->label('Exam Date'),
            Forms\Components\TextInput::make('total_marks')->label('Total Marks')->numeric()->default(100)->required()->minValue(1),
            Forms\Components\TextInput::make('passing_marks')->label('Passing Marks')->numeric()->default(40)->required()->minValue(0),
        ])->columns(2);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('exam.name')->label('Exam')->sortable()->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('schoolClass.name')->label('Class')->badge()->color('primary'),
            Tables\Columns\TextColumn::make('subject.name')->label('Subject')->badge()->color('info'),
            Tables\Columns\TextColumn::make('exam_date')->label('Date')->date('d M Y')->sortable(),
            Tables\Columns\TextColumn::make('total_marks')->label('Total')->suffix(' marks'),
            Tables\Columns\TextColumn::make('passing_marks')->label('Pass')->suffix(' marks'),
        ])->filters([
            Tables\Filters\SelectFilter::make('exam_id')->label('Exam')->options(fn()=>Exam::all()->pluck('name','id')),
            Tables\Filters\SelectFilter::make('school_class_id')->label('Class')->options(fn()=>SchoolClass::orderBy('numeric_order')->get()->pluck('name','id')),
        ])->actions([Tables\Actions\EditAction::make(),Tables\Actions\DeleteAction::make()])
          ->defaultSort('exam_id');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListExamSchedules::route('/'),'create'=>Pages\CreateExamSchedule::route('/create'),'edit'=>Pages\EditExamSchedule::route('/{record}/edit')];
    }
}