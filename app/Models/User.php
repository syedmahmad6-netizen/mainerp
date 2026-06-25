<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'email', 'phone',
        'password', 'role', 'is_active', 'profile_photo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Filament Panel Access Control ─────────────────────────────────────

    /**
     * Controls which Filament panel each user role can access.
     * Called automatically by Filament on every authenticated request.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'super-admin'  => $this->role === 'super_admin' && $this->is_active,
            'school-admin' => in_array($this->role, [
                'principal',
                'school_manager',
                'vice_principal',
                'teacher',
                'accountant',
                'librarian',
            ]) && $this->is_active,
            default => false,
        };
    }

    // ── Role Helpers ──────────────────────────────────────────────────────

    public function isSuperAdmin(): bool    { return $this->role === 'super_admin'; }
    public function isPrincipal(): bool     { return $this->role === 'principal'; }
    public function isSchoolManager(): bool { return $this->role === 'school_manager'; }
    public function isTeacher(): bool       { return $this->role === 'teacher'; }
    public function isStudent(): bool       { return $this->role === 'student'; }
    public function isParent(): bool        { return $this->role === 'parent'; }
    public function isAccountant(): bool    { return $this->role === 'accountant'; }

    // ── Profile Relationships ─────────────────────────────────────────────

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentProfile::class);
    }
}
