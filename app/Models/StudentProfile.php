<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentProfile extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'father_name',
        'admission_number',
        'roll_number',
        'date_of_birth',
        'gender',
        'blood_group',
        'b_form_number',
        'address',
        'city',
        'admission_date',
        'status',
        'current_section_id',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'admission_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'current_section_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            ParentProfile::class,
            'student_parent',
            'student_profile_id',
            'parent_profile_id'
        )->withPivot('is_primary_contact')->withTimestamps();
    }

    // ── Admission Number Generator ────────────────────────────────────────

    /**
     * Generates a unique, school-branded admission number.
     *
     * Format: {PREFIX}-{YEAR}-{SEQUENCE}
     * Example: GNO-2025-0001 (for subdomain "gnosis")
     *          DEM-2025-0042 (for subdomain "demo")
     *
     * PREFIX  = first 3 letters of school subdomain, uppercase
     * YEAR    = 4-digit year of admission
     * SEQUENCE = 4-digit zero-padded sequence (per school, per year)
     */
    public static function generateAdmissionNumber(): string
    {
        $school = tenant()->getSchool();
        $prefix = strtoupper(substr($school->subdomain, 0, 3));
        $year   = date('Y');

        // Find the highest sequence number for this school this year
        // Uses CAST to sort numerically, not alphabetically
        $lastNumber = static::where('admission_number', 'like', $prefix . '-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(admission_number, "-", -1) AS UNSIGNED) DESC')
            ->value('admission_number');

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, strrpos($lastNumber, '-') + 1);
            $sequence     = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Returns "Grade 5 – A" style label for display.
     */
    public function getSectionLabelAttribute(): string
    {
        return $this->section?->full_name ?? '—';
    }

    /**
     * Returns true if this student's class level requires portal login.
     */
    public function requiresStudentLogin(): bool
    {
        $level = $this->section?->schoolClass?->level;
        return in_array($level, ['middle', 'secondary', 'higher_secondary']);
    }
}
