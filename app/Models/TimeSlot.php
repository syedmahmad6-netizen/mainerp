<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TimeSlot extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','start_time','end_time','slot_order','is_break'];
    protected $casts = ['is_break' => 'boolean'];
    public function timetableEntries(): HasMany { return $this->hasMany(Timetable::class); }
    public function getTimeLabelAttribute(): string {
        $start = \Carbon\Carbon::parse($this->start_time)->format('h:i A');
        $end   = \Carbon\Carbon::parse($this->end_time)->format('h:i A');
        return "{$start} – {$end}";
    }
}
