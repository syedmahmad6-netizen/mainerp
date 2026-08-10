<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ExamSchedule extends Model {
    use BelongsToSchool;
    protected $fillable = [
        "exam_id","school_class_id","subject_id",
        "exam_date","total_marks","passing_marks",
    ];
    protected $casts = ["exam_date"=>"date"];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class,"subject_id","subject_id"); }
}