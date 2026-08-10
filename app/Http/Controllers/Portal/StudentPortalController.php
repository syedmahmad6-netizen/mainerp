<?php
namespace App\Http\Controllers\Portal;
use App\Http\Controllers\Controller;
use App\Models\{Announcement,Attendance,ExamResult,FeeCollection,Timetable,TimeSlot};
use Carbon\Carbon;
use Illuminate\Http\Request;
class StudentPortalController extends Controller {
    private function student() {
        $s = auth()->user()->studentProfile;
        abort_if(!$s, 403, 'Student profile not found.');
        return $s;
    }
    public function dashboard() {
        $student  = $this->student()->load(['user','section.schoolClass']);
        $from     = now()->startOfMonth()->format('Y-m-d');
        $to       = now()->endOfMonth()->format('Y-m-d');
        $attPct   = Attendance::getPercentage($student->id,$from,$to);
        $attSum   = Attendance::getSummary($student->id,$from,$to);
        $pendingFees  = FeeCollection::where('student_profile_id',$student->id)->whereIn('status',['pending','overdue'])->sum('balance');
        $latestResult = ExamResult::where('student_id',$student->id)->whereHas('exam',fn($q)=>$q->where('status','published'))->with('exam')->latest()->first();
        $announcements = Announcement::whereIn('target_role',['all','students'])->where('is_archived',false)
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->latest('published_at')->take(4)->get();
        $todaySlots = $student->current_section_id
            ? Timetable::where('section_id',$student->current_section_id)->where('day_of_week',now()->dayOfWeekIso)->with(['subject','teacher','timeSlot'])->orderBy('time_slot_id')->get()
            : null;
        return view('portal.student.dashboard',compact('student','attPct','attSum','pendingFees','latestResult','announcements','todaySlots'));
    }
    public function attendance(Request $request) {
        $student = $this->student();
        $month   = $request->get('month', now()->format('Y-m'));
        $from    = Carbon::parse($month.'-01')->startOfMonth();
        $to      = $from->copy()->endOfMonth();
        $records = Attendance::where('student_id',$student->id)->whereBetween('date',[$from,$to])->orderBy('date')->get()->keyBy(fn($r)=>$r->date->format('Y-m-d'));
        $summary = Attendance::getSummary($student->id,$from->format('Y-m-d'),$to->format('Y-m-d'));
        $pct     = Attendance::getPercentage($student->id,$from->format('Y-m-d'),$to->format('Y-m-d'));
        return view('portal.student.attendance',compact('student','records','summary','pct','month','from','to'));
    }
    public function results() {
        $student = $this->student();
        $exams   = ExamResult::where('student_id',$student->id)->with(['exam.examType','subject'])->get()
            ->groupBy('exam_id')->map(function($results) {
                $exam=$results->first()->exam; $total=$results->sum('total_marks'); $obtained=$results->sum('obtained_marks');
                $pct=$total>0?round(($obtained/$total)*100,1):0;
                return ['exam'=>$exam,'results'=>$results,'total_obtained'=>$obtained,'total_marks'=>$total,'percentage'=>$pct,
                    'grade'=>\App\Models\ExamResult::calculateGrade($pct,tenant()->getSchoolId())];
            })->filter(fn($e)=>$e['exam']->status==='published')->sortByDesc(fn($e)=>$e['exam']->created_at);
        return view('portal.student.results',compact('student','exams'));
    }
    public function fees() {
        $student  = $this->student();
        $records  = FeeCollection::where('student_profile_id',$student->id)->with('feeType')->orderBy('fee_month','desc')->get();
        $totalDue = $records->whereIn('status',['pending','overdue'])->sum('balance');
        return view('portal.student.fees',compact('student','records','totalDue'));
    }
    public function timetable() {
        $student  = $this->student()->load('section.schoolClass');
        $section  = $student->section;
        $slots    = TimeSlot::orderBy('slot_order')->get();
        $days     = Timetable::allDays((tenant()->getSchool()->settings['working_days']??'mon_fri')==='mon_sat');
        $entries  = $section?Timetable::where('section_id',$section->id)->with(['subject','teacher','timeSlot'])->get():collect();
        $grid=[]; foreach($entries as $e){$grid[$e->day_of_week][$e->time_slot_id]=$e;}
        return view('portal.student.timetable',compact('student','slots','days','grid'));
    }
    public function announcements() {
        $student = $this->student();
        $announcements = Announcement::whereIn('target_role',['all','students'])->where('is_archived',false)
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->latest('published_at')->paginate(10);
        return view('portal.student.announcements',compact('student','announcements'));
    }
}