<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use BelongsToSchool;

    // Explicit table name — "class" is a PHP reserved word
    protected $table = 'school_classes';

    protected $fillable = [
        'school_id', 'name', 'numeric_order', 'level',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subjects')
                    ->withPivot(['academic_year_id', 'weekly_periods'])
                    ->withTimestamps();
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'pre_primary'      => 'Pre-Primary',
            'primary'          => 'Primary',
            'middle'           => 'Middle',
            'secondary'        => 'Secondary (Matric)',
            'higher_secondary' => 'Higher Secondary (Inter)',
            default            => $this->level,
        };
    }
}
