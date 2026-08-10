<?php

namespace App\Models\Concerns;

use App\Support\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToSchool Trait
 *
 * Apply to ANY model that belongs to a school (tenant).
 * Automatically:
 *   1. Scopes all READ queries to the current school_id.
 *   2. Assigns school_id on CREATE so you never forget it.
 *   3. Adds a school() relationship to every model.
 *
 * Usage: Add `use BelongsToSchool;` to your model.
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        // ─── Global Scope: Auto-filter all queries by current school ──────
        static::addGlobalScope('school', function (Builder $builder) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            // Only scope if a tenant has been identified for this request.
            // Super Admin has no tenant set — so it sees ALL records.
            if ($tenantManager->isSet()) {
                $builder->where(
                    (new static)->getTable() . '.school_id',
                    $tenantManager->getSchoolId()
                );
            }
        });

        // ─── Auto-assign school_id on model creation ──────────────────────
        static::creating(function ($model) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isSet() && empty($model->school_id)) {
                $model->school_id = $tenantManager->getSchoolId();
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class);
    }
}
