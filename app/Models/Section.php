<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'school_class_id', 'academic_year_id',
        'class_teacher_id', 'name', 'capacity',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'current_section_id');
    }

    // "Grade 5 – A" — used in dropdowns throughout the app
    public function getFullNameAttribute(): string
    {
        return $this->schoolClass->name . ' – ' . $this->name;
    }
}
