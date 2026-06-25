<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeAssignment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_profile_id', 'fee_structure_id',
        'academic_year_id', 'discount_type', 'discount_value',
        'discount_reason', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Calculate the discounted amount for this assignment.
     */
    public function getDiscountedAmountAttribute(): float
    {
        $amount = $this->feeStructure->amount;

        return match ($this->discount_type) {
            'percentage' => $amount - ($amount * $this->discount_value / 100),
            'fixed'      => max(0, $amount - $this->discount_value),
            default      => $amount,
        };
    }
}
