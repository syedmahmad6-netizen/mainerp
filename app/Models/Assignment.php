<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Assignment extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','section_id','subject_id','teacher_id','title','description','due_date','attachment','allow_submission','status'];
    protected $casts = ['due_date'=>'date','allow_submission'=>'boolean'];
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
    public function isOverdue(): bool { return $this->due_date->isPast() && $this->status==='active'; }
}