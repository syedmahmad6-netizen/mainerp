<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ExamResult extends Model {
    use BelongsToSchool;
    protected $fillable = [
        "school_id","exam_id","student_id","subject_id",
        "obtained_marks","total_marks","grade","percentage",
        "is_pass","remarks","entered_by",
    ];
    protected $casts = [
        "obtained_marks"=>"decimal:2",
        "percentage"=>"decimal:2",
        "is_pass"=>"boolean",
    ];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class,"student_id"); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function enteredBy(): BelongsTo { return $this->belongsTo(User::class,"entered_by"); }
    public static function calculateGrade(float $percentage, int $schoolId): string {
        $scale = GradingScale::where("school_id",$schoolId)
            ->where("min_percent","<=",$percentage)
            ->where("max_percent",">=",$percentage)
            ->first();
        if ($scale) return $scale->grade;
        return match(true) {
            $percentage >= 90 => "A1",
            $percentage >= 80 => "A",
            $percentage >= 70 => "B",
            $percentage >= 60 => "C",
            $percentage >= 50 => "D",
            $percentage >= 40 => "E",
            default           => "F",
        };
    }
}