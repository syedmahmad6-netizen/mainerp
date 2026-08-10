<?php
namespace App\Filament\SchoolAdmin\Pages;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class EnterMarks extends Page {
    protected static ?string $navigationIcon  = "heroicon-o-pencil-square";
    protected static ?string $navigationLabel = "Enter Marks";
    protected static ?string $navigationGroup = "Academics";
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = "filament.school-admin.pages.enter-marks";

    public ?array $data = [];
    public int $totalMarks = 0;
    public int $passingMarks = 40;

    public function mount(): void {
        $this->form->fill(["exam_id"=>null,"section_id"=>null,"subject_id"=>null,"marks"=>[]]);
    }

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make("Select Exam, Class & Subject")->schema([
                Forms\Components\Select::make("exam_id")
                    ->label("Exam")->options(fn()=>Exam::whereIn("status",["active","draft"])->get()->pluck("name","id"))
                    ->required()->searchable()->live()
                    ->afterStateUpdated(fn(Forms\Set $set)=>$set("marks",[])),
                Forms\Components\Select::make("section_id")
                    ->label("Class & Section")
                    ->options(fn()=>Section::with("schoolClass")->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name." - ".$s->name]))
                    ->required()->searchable()->live()
                    ->afterStateUpdated(fn(Forms\Set $set)=>$set("marks",[])),
                Forms\Components\Select::make("subject_id")
                    ->label("Subject")
                    ->options(function(Forms\Get $get) {
                        $examId=$get("exam_id"); $sectionId=$get("section_id");
                        if(!$examId||!$sectionId) return [];
                        $section=Section::with("schoolClass")->find($sectionId);
                        if(!$section) return [];
                        return ExamSchedule::where("exam_id",$examId)->where("school_class_id",$section->school_class_id)
                            ->with("subject")->get()->mapWithKeys(fn($s)=>[$s->subject_id=>$s->subject->name." (out of ".$s->total_marks.")"]);
                    })->required()->searchable()->live()
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get, $state) {
                        $this->loadMarks($set,$get("exam_id"),$get("section_id"),$state);
                    }),
            ])->columns(3),
            Forms\Components\Section::make("Student Marks")->schema([
                Forms\Components\Repeater::make("marks")->label("")
                    ->schema([
                        Forms\Components\Placeholder::make("student_name")->label("Student")->content(fn(Forms\Get $get)=>$get("student_name")??"—"),
                        Forms\Components\TextInput::make("obtained_marks")->label("Marks Obtained")->numeric()->minValue(0)->required()
                            ->live(onBlur:true)
                            ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get, $state) {
                                if($state!==null&&$this->totalMarks>0) {
                                    $pct=round(($state/$this->totalMarks)*100,2);
                                    $set("percentage",$pct);
                                    $set("grade",ExamResult::calculateGrade($pct,tenant()->getSchoolId()));
                                    $set("is_pass",$state>=$this->passingMarks);
                                }
                            }),
                        Forms\Components\TextInput::make("percentage")->label("%")->numeric()->readOnly()->suffix("%"),
                        Forms\Components\TextInput::make("grade")->label("Grade")->readOnly()->maxLength(5),
                        Forms\Components\Hidden::make("student_id"),
                        Forms\Components\Hidden::make("student_name"),
                        Forms\Components\Hidden::make("is_pass"),
                        Forms\Components\TextInput::make("remarks")->label("Remarks")->placeholder("Optional...")->maxLength(100),
                    ])->columns(5)->addable(false)->deletable(false)->reorderable(false)
                    ->visible(fn(Forms\Get $get)=>!empty($get("subject_id")))
                    ->defaultItems(0),
            ]),
        ])->statePath("data");
    }

    private function loadMarks(Forms\Set $set, ?int $examId, ?int $sectionId, ?int $subjectId): void {
        if(!$examId||!$sectionId||!$subjectId){$set("marks",[]);return;}
        $section=Section::with("schoolClass")->find($sectionId);
        if(!$section){$set("marks",[]);return;}
        $schedule=ExamSchedule::where("exam_id",$examId)->where("school_class_id",$section->school_class_id)->where("subject_id",$subjectId)->first();
        if($schedule){$this->totalMarks=$schedule->total_marks;$this->passingMarks=$schedule->passing_marks;}
        $students=StudentProfile::where("current_section_id",$sectionId)->where("status","active")->with("user")->orderBy("id")->get();
        $existing=ExamResult::where("exam_id",$examId)->where("subject_id",$subjectId)->whereIn("student_id",$students->pluck("id"))->get()->keyBy("student_id");
        $rows=$students->map(fn($s)=>[
            "student_id"=>$s->id,"student_name"=>$s->user->name??"Unknown",
            "obtained_marks"=>$existing[$s->id]?->obtained_marks??null,
            "percentage"=>$existing[$s->id]?->percentage??null,
            "grade"=>$existing[$s->id]?->grade??null,
            "is_pass"=>$existing[$s->id]?->is_pass??false,
            "remarks"=>$existing[$s->id]?->remarks??"",
        ])->values()->toArray();
        $set("marks",$rows);
    }

    public function save(): void {
        $data=$this->form->getState();
        if(empty($data["subject_id"])||empty($data["marks"])){
            Notification::make()->title("Please select exam, section, and subject first.")->warning()->send();
            return;
        }
        $section=Section::with("schoolClass")->find($data["section_id"]);
        $schedule=ExamSchedule::where("exam_id",$data["exam_id"])->where("school_class_id",$section->school_class_id)->where("subject_id",$data["subject_id"])->first();
        $totalMarks=$schedule?->total_marks??100;
        $passingMarks=$schedule?->passing_marks??40;
        $invalid=collect($data["marks"])->filter(fn($r)=>isset($r["obtained_marks"])&&$r["obtained_marks"]>$totalMarks);
        if($invalid->count()>0){
            Notification::make()->title("Marks exceed total ({$totalMarks})")->body("Please check marks for: ".$invalid->pluck("student_name")->join(", "))->danger()->persistent()->send();
            return;
        }
        DB::transaction(function() use($data,$totalMarks,$passingMarks){
            foreach($data["marks"] as $row){
                if(!isset($row["obtained_marks"])||$row["obtained_marks"]===null) continue;
                $obtained=(float)$row["obtained_marks"];
                $pct=round(($obtained/$totalMarks)*100,2);
                $grade=ExamResult::calculateGrade($pct,tenant()->getSchoolId());
                ExamResult::updateOrCreate(
                    ["school_id"=>tenant()->getSchoolId(),"exam_id"=>$data["exam_id"],"student_id"=>$row["student_id"],"subject_id"=>$data["subject_id"]],
                    ["obtained_marks"=>$obtained,"total_marks"=>$totalMarks,"grade"=>$grade,"percentage"=>$pct,"is_pass"=>$obtained>=$passingMarks,"remarks"=>$row["remarks"]??null,"entered_by"=>auth()->id()]
                );
            }
        });
        $saved=collect($data["marks"])->filter(fn($r)=>isset($r["obtained_marks"]))->count();
        Notification::make()->title("Marks saved for {$saved} students!")->success()->send();
    }

    protected function getFormActions(): array {
        return [Action::make("save")->label("Save Marks")->submit("save")->icon("heroicon-o-check")];
    }
}