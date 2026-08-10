<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Exam extends Model {
    use BelongsToSchool;
    protected $fillable = [
        "school_id","exam_type_id","academic_year_id",
        "name","start_date","end_date","status",
    ];
    protected $casts = ["start_date"=>"date","end_date"=>"date"];
    public function examType(): BelongsTo { return $this->belongsTo(ExamType::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function schedules(): HasMany { return $this->hasMany(ExamSchedule::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }
    public function isPublished(): bool { return $this->status === "published"; }
    public function isDraft(): bool { return $this->status === "draft"; }
}