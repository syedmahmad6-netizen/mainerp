<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance';

    protected $fillable = [
        'school_id', 'section_id', 'student_id', 'teacher_id',
        'subject_id', 'date', 'status', 'remarks',
        'edited_by', 'edit_reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // ── Static Helpers ────────────────────────────────────────────────────

    /**
     * Get attendance percentage for a student in a date range.
     * Returns a float like 87.5 (percent).
     */
    public static function getPercentage(int $studentId, string $from, string $to): float
    {
        $total   = static::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->count();

        if ($total === 0) return 0;

        $present = static::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['present', 'late']) // Late counts as present for %
            ->count();

        return round(($present / $total) * 100, 1);
    }

    /**
     * Get summary counts for a student in a date range.
     * Returns ['present' => N, 'absent' => N, 'late' => N, 'leave' => N, 'total' => N]
     */
    public static function getSummary(int $studentId, string $from, string $to): array
    {
        $records = static::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'present' => $records['present'] ?? 0,
            'absent'  => $records['absent']  ?? 0,
            'late'    => $records['late']    ?? 0,
            'leave'   => $records['leave']   ?? 0,
            'total'   => array_sum($records),
        ];
    }
}
