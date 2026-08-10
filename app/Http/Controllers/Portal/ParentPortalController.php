<?php
namespace App\Http\Controllers\Portal;
use App\Http\Controllers\Controller;
use App\Models\{Announcement,Attendance,ExamResult,FeeCollection,StudentProfile,Timetable,TimeSlot};
use Carbon\Carbon;
use Illuminate\Http\Request;
class ParentPortalController extends Controller {
    private function getChildren() {
        $parent = auth()->user()->parentProfile;
        abort_if(!$parent, 403, 'Parent profile not found.');
        return $parent->students()->with(['user','section.schoolClass','section.academicYear'])->where('status','active')->get();
    }
    private function authorizeChild(StudentProfile $student): void {
        abort_unless(auth()->user()->parentProfile->students()->where('student_profile_id',$student->id)->exists(), 403);
    }
    public function dashboard() {
        $children = $this->getChildren();
        $childStats = $children->map(function($s) {
            $from = now()->startOfMonth()->format('Y-m-d');
            $to   = now()->endOfMonth()->format('Y-m-d');
            return ['student'=>$s,'attendance_pct'=>Attendance::getPercentage($s->id,$from,$to),
                'pending_fees'=>FeeCollection::where('student_profile_id',$s->id)->whereIn('status',['pending','overdue'])->sum('balance'),
                'latest_exam'=>ExamResult::where('student_id',$s->id)->with('exam')->latest()->first()];
        });
        $announcements = Announcement::whereIn('target_role',['all','parents'])->where('is_archived',false)
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->latest('published_at')->take(5)->get();
        return view('portal.parent.dashboard', compact('childStats','announcements'));
    }
    public function attendance(Request $request, StudentProfile $student) {
        $this->authorizeChild($student);
        $month = $request->get('month', now()->format('Y-m'));
        $from  = Carbon::parse($month.'-01')->startOfMonth();
        $to    = $from->copy()->endOfMonth();
        $records = Attendance::where('student_id',$student->id)->whereBetween('date',[$from,$to])->orderBy('date')->get()->keyBy(fn($r)=>$r->date->format('Y-m-d'));
        $summary = Attendance::getSummary($student->id,$from->format('Y-m-d'),$to->format('Y-m-d'));
        $pct     = Attendance::getPercentage($student->id,$from->format('Y-m-d'),$to->format('Y-m-d'));
        return view('portal.parent.attendance', compact('student','records','summary','pct','month','from','to'));
    }
    public function results(StudentProfile $student) {
        $this->authorizeChild($student);
        $exams = ExamResult::where('student_id',$student->id)->with(['exam.examType','subject'])->get()
            ->groupBy('exam_id')->map(function($results) {
                $exam=$results->first()->exam; $total=$results->sum('total_marks'); $obtained=$results->sum('obtained_marks');
                $pct=$total>0?round(($obtained/$total)*100,1):0;
                return ['exam'=>$exam,'results'=>$results,'total_obtained'=>$obtained,'total_marks'=>$total,'percentage'=>$pct,
                    'grade'=>\App\Models\ExamResult::calculateGrade($pct,tenant()->getSchoolId())];
            })->filter(fn($e)=>$e['exam']->status==='published')->sortByDesc(fn($e)=>$e['exam']->created_at);
        return view('portal.parent.results', compact('student','exams'));
    }
    public function fees(StudentProfile $student) {
        $this->authorizeChild($student);
        $records  = FeeCollection::where('student_profile_id',$student->id)->with('feeType')->orderBy('fee_month','desc')->get();
        $totalDue = $records->whereIn('status',['pending','overdue'])->sum('balance');
        return view('portal.parent.fees', compact('student','records','totalDue'));
    }
    public function timetable(StudentProfile $student) {
        $this->authorizeChild($student);
        $section=$student->section; $slots=TimeSlot::orderBy('slot_order')->get();
        $days=Timetable::allDays((tenant()->getSchool()->settings['working_days']??'mon_fri')==='mon_sat');
        $entries=$section?Timetable::where('section_id',$section->id)->with(['subject','teacher','timeSlot'])->get():collect();
        $grid=[]; foreach($entries as $e){$grid[$e->day_of_week][$e->time_slot_id]=$e;}
        return view('portal.parent.timetable', compact('student','slots','days','grid'));
    }
    public function announcements() {
        $announcements = Announcement::whereIn('target_role',['all','parents'])->where('is_archived',false)
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->latest('published_at')->paginate(10);
        return view('portal.parent.announcements', compact('announcements'));
    }
}