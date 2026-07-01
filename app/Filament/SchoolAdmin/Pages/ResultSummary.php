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
use Filament\Pages\Page;

class ResultSummary extends Page {
    protected static ?string $navigationIcon  = "heroicon-o-trophy";
    protected static ?string $navigationLabel = "Result Summary";
    protected static ?string $navigationGroup = "Academics";
    protected static ?int    $navigationSort  = 11;
    protected static string  $view            = "filament.school-admin.pages.result-summary";

    public ?array $data    = [];
    public array  $results = [];
    public string $examName = "";
    public string $sectionName = "";

    public function mount(): void {
        $this->form->fill(["exam_id"=>null,"section_id"=>null]);
    }

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make("exam_id")->label("Exam")->options(fn()=>Exam::all()->pluck("name","id"))->required()->searchable(),
            Forms\Components\Select::make("section_id")->label("Class & Section")
                ->options(fn()=>Section::with("schoolClass")->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name." - ".$s->name]))
                ->required()->searchable(),
        ])->statePath("data")->columns(2);
    }

    public function generate(): void {
        $data=$this->form->getState();
        $exam=Exam::find($data["exam_id"]);
        $section=Section::with("schoolClass")->find($data["section_id"]);
        if(!$exam||!$section){return;}
        $this->examName=$exam->name;
        $this->sectionName=$section->schoolClass->name." - ".$section->name;
        $students=StudentProfile::where("current_section_id",$data["section_id"])->where("status","active")->with("user")->get();
        $schedules=ExamSchedule::where("exam_id",$data["exam_id"])->where("school_class_id",$section->school_class_id)->with("subject")->get();
        $subjects=$schedules->mapWithKeys(fn($s)=>[$s->subject_id=>["name"=>$s->subject->name,"total"=>$s->total_marks,"passing"=>$s->passing_marks]]);
        $allResults=ExamResult::where("exam_id",$data["exam_id"])->whereIn("student_id",$students->pluck("id"))->get()->groupBy("student_id");
        $summary=$students->map(function($student) use($subjects,$allResults){
            $studentResults=$allResults[$student->id]??collect();
            $subjectBreakdown=[];
            $totalObtained=0; $totalMarks=0; $allPass=true;
            foreach($subjects as $subjectId=>$sub){
                $result=$studentResults->firstWhere("subject_id",$subjectId);
                $obtained=$result?->obtained_marks??null;
                $total=$sub["total"];
                $isPass=$obtained!==null&&$obtained>=$sub["passing"];
                $subjectBreakdown[$subjectId]=["obtained"=>$obtained,"total"=>$total,"grade"=>$result?->grade??"—","is_pass"=>$isPass];
                if($obtained!==null){$totalObtained+=$obtained;$totalMarks+=$total;}
                if(!$isPass) $allPass=false;
            }
            $percentage=$totalMarks>0?round(($totalObtained/$totalMarks)*100,1):0;
            return ["student_id"=>$student->id,"name"=>$student->user->name??"—","admission_no"=>$student->admission_number??"—","subjects"=>$subjectBreakdown,"total_obtained"=>$totalObtained,"total_marks"=>$totalMarks,"percentage"=>$percentage,"overall_grade"=>\App\Models\ExamResult::calculateGrade($percentage,tenant()->getSchoolId()),"result"=>$allPass&&$totalMarks>0?"PASS":"FAIL","rank"=>0];
        })->sortByDesc("total_obtained")->values();
$rank=1;
$summaryArray = $summary->toArray();
        $rank = 1;
        foreach ($summaryArray as $i => $row) {
            if ($i > 0 && $row['total_obtained'] === $summaryArray[$i-1]['total_obtained']) {
                $summaryArray[$i]['rank'] = $summaryArray[$i-1]['rank'];
            } else {
                $summaryArray[$i]['rank'] = $rank;
            }
            $rank++;
        }
        $this->results = $summaryArray;
$this->results=$summary->toArray();
    }

    protected function getFormActions(): array {
        return [Action::make("generate")->label("Generate Results")->submit("generate")->icon("heroicon-o-magnifying-glass")];
    }
}