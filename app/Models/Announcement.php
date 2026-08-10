<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Announcement extends Model {
    use BelongsToSchool;
    protected $fillable = [
        'school_id','title','body','type','target_role','section_id',
        'attachment','send_sms','created_by','published_at','expires_at','is_archived',
    ];
    protected $casts = [
        'send_sms'     => 'boolean',
        'is_archived'  => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function reads(): HasMany { return $this->hasMany(AnnouncementRead::class); }
    public function isExpired(): bool { return $this->expires_at && $this->expires_at->isPast(); }
    public function isActive(): bool { return !$this->is_archived && !$this->isExpired(); }
    public function getTypeLabelAttribute(): string {
        return match($this->type) {
            'general'       => 'General',
            'urgent'        => 'Urgent',
            'holiday'       => 'Holiday Notice',
            'event'         => 'Event',
            'exam_schedule' => 'Exam Schedule',
            default         => $this->type,
        };
    }
    public function getScopeAttribute(): string {
        return $this->section ? $this->section->full_name : 'School-Wide';
    }
}