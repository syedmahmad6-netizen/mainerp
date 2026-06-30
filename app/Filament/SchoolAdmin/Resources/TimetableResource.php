<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\TimetableResource\Pages;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Section;
use App\Models\TeacherProfile;
use App\Models\TimeSlot;
use App\Models\Timetable;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class TimetableResource extends Resource {
    protected static ?string $model           = Timetable::class;
    protected static ?string $navigationIcon  = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Timetable Entries';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 6;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Schedule Details')->schema([
                Forms\Components\Select::make('academic_year_id')->label('Academic Year')->options(fn()=>AcademicYear::all()->pluck('name','id'))->default(fn()=>AcademicYear::where('is_current',true)->value('id'))->required(),
                Forms\Components\Select::make('section_id')->label('Class & Section')->options(fn()=>Section::with('schoolClass')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name.' – '.$s->name]))->required()->searchable()->live(),
                Forms\Components\Select::make('day_of_week')->label('Day')->options([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'])->required(),
                Forms\Components\Select::make('time_slot_id')->label('Period / Time Slot')->options(fn()=>TimeSlot::orderBy('slot_order')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->name.' ('.$s->time_label.'])']))->required(),
                Forms\Components\Select::make('subject_id')->label('Subject')->options(function(Forms\Get $get){
                    $sid=$get('section_id');
                    if(!$sid) return \App\Models\Subject::all()->pluck('name','id');
                    $section=Section::with('schoolClass')->find($sid);
                    if(!$section) return [];
                    return ClassSubject::where('school_class_id',$section->school_class_id)->with('subject')->get()->mapWithKeys(fn($cs)=>[$cs->subject_id=>$cs->subject->name]);
                })->required()->searchable()->live(),
                Forms\Components\Select::make('teacher_id')->label('Teacher')->options(function(Forms\Get $get){
                    $subjectId=$get('subject_id');
                    if(!$subjectId) return User::where('role','teacher')->where('is_active',true)->pluck('name','id');
                    $qualified=TeacherProfile::whereHas('subjects',fn($q)=>$q->where('subjects.id',$subjectId))->with('user')->get();
                    if($qualified->isEmpty()) return User::where('role','teacher')->where('is_active',true)->pluck('name','id');
                    return $qualified->mapWithKeys(fn($t)=>[$t->user_id=>$t->user->name??'Unknown']);
                })->required()->searchable(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('section.schoolClass.name')->label('Class')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('section.name')->label('Section')->badge()->color('primary'),
            Tables\Columns\TextColumn::make('day_of_week')->label('Day')->formatStateUsing(fn($state)=>Timetable::dayName($state))->badge()->color('gray')->sortable(),
            Tables\Columns\TextColumn::make('timeSlot.name')->label('Period')->sortable(),
            Tables\Columns\TextColumn::make('timeSlot.start_time')->label('Time')->formatStateUsing(fn($state)=>$state?\Carbon\Carbon::parse($state)->format('h:i A'):'—'),
            Tables\Columns\TextColumn::make('subject.name')->label('Subject')->badge()->color('info')->searchable(),
            Tables\Columns\TextColumn::make('teacher.name')->label('Teacher')->searchable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('section_id')->label('Section')->options(fn()=>Section::with('schoolClass')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name.' – '.$s->name])),
            Tables\Filters\SelectFilter::make('day_of_week')->label('Day')->options([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday']),
            Tables\Filters\SelectFilter::make('teacher_id')->label('Teacher')->options(fn()=>User::where('role','teacher')->pluck('name','id')),
        ])->actions([Tables\Actions\EditAction::make(),Tables\Actions\DeleteAction::make()])->defaultSort('day_of_week');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListTimetable::route('/'),'create'=>Pages\CreateTimetable::route('/create'),'edit'=>Pages\EditTimetable::route('/{record}/edit')];
    }
}
