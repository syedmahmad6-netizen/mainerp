<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeChallan extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_profile_id', 'academic_year_id',
        'challan_no', 'fee_month', 'due_date', 'total_amount',
        'status', 'generated_at',
    ];

    protected $casts = [
        'fee_month'    => 'date',
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Generates a unique challan number.
     * Format: GNO-CHN-2025-0001
     */
    public static function generateChallanNumber(): string
    {
        $school = tenant()->getSchool();
        $prefix = strtoupper(substr($school->subdomain, 0, 3));
        $year   = date('Y');

        $last = static::where('challan_no', 'like', $prefix . '-CHN-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(challan_no, "-", -1) AS UNSIGNED) DESC')
            ->value('challan_no');

        $sequence = $last
            ? (int) substr($last, strrpos($last, '-') + 1) + 1
            : 1;

        return $prefix . '-CHN-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
