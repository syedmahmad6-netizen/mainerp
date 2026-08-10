<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ExamType extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id', 'name'];
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
}