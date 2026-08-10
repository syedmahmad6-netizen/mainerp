<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentProfile extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'user_id', 'cnic', 'occupation',
        'relationship', 'emergency_contact', 'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            StudentProfile::class,
            'student_parent',
            'parent_profile_id',
            'student_profile_id'
        )->withPivot('is_primary_contact')->withTimestamps();
    }
}
