<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'school_class_id', 'fee_type_id',
        'academic_year_id', 'amount', 'frequency', 'is_active',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentFeeAssignment::class);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'monthly'   => 'Monthly',
            'quarterly' => 'Quarterly',
            'annual'    => 'Annual',
            'one_time'  => 'One-Time',
            default     => $this->frequency,
        };
    }
}
