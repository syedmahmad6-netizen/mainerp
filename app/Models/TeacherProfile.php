<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeacherProfile extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'employee_id',
        'cnic',
        'qualification',
        'specialization',
        'date_of_birth',
        'gender',
        'joining_date',
        'salary',
        'employment_type',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date'  => 'date',
        'salary'        => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects',
            'teacher_profile_id',
            'subject_id'
        )->withTimestamps();
    }

    // ── Employee ID Generator ─────────────────────────────────────────────

    /**
     * Generates a unique, school-branded employee ID.
     *
     * Format : {PREFIX}-TCH-{YEAR}-{SEQUENCE}
     * Example: GNO-TCH-2025-001
     *
     * PREFIX   = first 3 letters of school subdomain, uppercase
     * TCH      = fixed identifier for teachers (STF for other staff later)
     * YEAR     = joining year
     * SEQUENCE = 3-digit zero-padded sequence per school per year
     */
    public static function generateEmployeeId(): string
    {
        $school = tenant()->getSchool();
        $prefix = strtoupper(substr($school->subdomain, 0, 3));
        $year   = date('Y');

        $lastRecord = static::where('employee_id', 'like', $prefix . '-TCH-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(employee_id, "-", -1) AS UNSIGNED) DESC')
            ->value('employee_id');

        $sequence = $lastRecord
            ? (int) substr($lastRecord, strrpos($lastRecord, '-') + 1) + 1
            : 1;

        return $prefix . '-TCH-' . $year . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function getSubjectListAttribute(): string
    {
        return $this->subjects->pluck('name')->join(', ') ?: '—';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
