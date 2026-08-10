<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'start_date', 'end_date', 'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * When setting a year as current, automatically un-set all other
     * years for this school. Only one current year allowed at a time.
     */
    protected static function booted(): void
    {
        static::saving(function (AcademicYear $year) {
            if ($year->is_current && $year->isDirty('is_current')) {
                static::withoutGlobalScope('school')
                    ->where('school_id', $year->school_id)
                    ->where('id', '!=', $year->id ?? 0)
                    ->update(['is_current' => false]);
            }
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }
}
