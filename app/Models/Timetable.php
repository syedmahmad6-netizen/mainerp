<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Timetable extends Model {
    use BelongsToSchool;
    protected $table = 'timetable';
    protected $fillable = ['school_id','section_id','subject_id','teacher_id','time_slot_id','academic_year_id','day_of_week'];
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function timeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public static function dayName(int $day): string {
        return match($day){1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',default=>'Unknown'};
    }
    public static function allDays(bool $includeSaturday=false): array {
        $d=[1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
        if($includeSaturday) $d[6]='Saturday';
        return $d;
    }
    public function getDayNameAttribute(): string { return static::dayName($this->day_of_week); }
}
