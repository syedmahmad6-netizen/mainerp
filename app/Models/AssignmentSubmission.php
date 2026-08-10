<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssignmentSubmission extends Model {
    public $timestamps = false;
    protected $fillable = ['assignment_id','student_id','file','remarks','status','marks','submitted_at'];
    protected $casts = ['submitted_at'=>'datetime','marks'=>'decimal:2'];
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class,'student_id'); }
}