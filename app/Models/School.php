<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * School — The Tenant model.
 * Does NOT use BelongsToSchool trait because it IS the tenant, not a child of one.
 */
class School extends Model
{
    protected $fillable = [
        'name', 'subdomain', 'address', 'city', 'province',
        'phone', 'email', 'logo', 'is_active', 'plan',
        'trial_ends_at', 'settings',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'trial_ends_at' => 'date',
        'settings'      => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function getPortalUrlAttribute(): string
    {
        return 'https://' . $this->subdomain . '.' . config('tenancy.base_domain');
    }

    public function isOnTrial(): bool
    {
        return $this->plan === 'trial' && $this->trial_ends_at?->isFuture();
    }

    /**
     * Get a specific setting value with a default fallback.
     * Usage: $school->setting('fee_due_day', 10)
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $defaults = [
            'grading_system'  => 'percentage',
            'currency'        => 'PKR',
            'timezone'        => 'Asia/Karachi',
            'fee_due_day'     => 10,
            'pass_percentage' => 40,
            'sms_enabled'     => false,
            'language'        => 'en',
        ];

        return $this->settings[$key] ?? $defaults[$key] ?? $default;
    }
}
