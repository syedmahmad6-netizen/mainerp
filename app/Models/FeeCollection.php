<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCollection extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_profile_id', 'fee_type_id', 'fee_structure_id',
        'academic_year_id', 'fee_month', 'amount_due', 'discount', 'fine',
        'amount_paid', 'balance', 'status', 'paid_date', 'receipt_no',
        'payment_method', 'collected_by', 'remarks',
    ];

    protected $casts = [
        'fee_month'   => 'date',
        'paid_date'   => 'date',
        'amount_due'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'fine'        => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance'     => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // ── Receipt Number Generator ──────────────────────────────────────────

    /**
     * Generates a unique school-branded receipt number.
     *
     * Format : {PREFIX}-RCP-{YEAR}-{SEQUENCE}
     * Example: GNO-RCP-2025-0001
     */
    public static function generateReceiptNumber(): string
    {
        $school = tenant()->getSchool();
        $prefix = strtoupper(substr($school->subdomain, 0, 3));
        $year   = date('Y');

        $last = static::where('receipt_no', 'like', $prefix . '-RCP-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(receipt_no, "-", -1) AS UNSIGNED) DESC')
            ->value('receipt_no');

        $sequence = $last
            ? (int) substr($last, strrpos($last, '-') + 1) + 1
            : 1;

        return $prefix . '-RCP-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'overdue', 'partial']);
    }

    public function getFeeMonthLabelAttribute(): string
    {
        return $this->fee_month?->format('F Y') ?? '—'; // "June 2025"
    }
}
