<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSettings extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'type', 'low_threshold_percent',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get or create settings for the current tenant school.
     * Seeds defaults (daily, 75%) if not yet configured.
     */
    public static function forCurrentSchool(): static
    {
        return static::firstOrCreate(
            ['school_id' => tenant()->getSchoolId()],
            [
                'type'                  => 'daily',
                'low_threshold_percent' => 75,
            ]
        );
    }
}
