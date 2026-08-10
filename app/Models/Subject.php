<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'is_core', 'color',
    ];

    protected $casts = [
        'is_core' => 'boolean',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subjects')
                    ->withPivot(['academic_year_id', 'weekly_periods'])
                    ->withTimestamps();
    }
}
