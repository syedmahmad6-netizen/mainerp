<?php

namespace App\Support;

use App\Models\School;

/**
 * TenantManager — Singleton that holds the current tenant (school)
 * for the entire lifecycle of a request.
 *
 * Usage: app(TenantManager::class)->getSchool()
 * Or via helper: tenant()->getSchoolId()
 */
class TenantManager
{
    protected ?School $currentSchool = null;

    public function setSchool(School $school): void
    {
        $this->currentSchool = $school;
    }

    public function getSchool(): ?School
    {
        return $this->currentSchool;
    }

    public function getSchoolId(): ?int
    {
        return $this->currentSchool?->id;
    }

    public function isSet(): bool
    {
        return $this->currentSchool !== null;
    }
}
