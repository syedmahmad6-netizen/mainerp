<?php

use App\Support\TenantManager;

if (! function_exists('tenant')) {
    /**
     * Returns the TenantManager singleton for the current request.
     *
     * Usage anywhere in the app:
     *   tenant()->getSchool()        → School model
     *   tenant()->getSchoolId()      → int
     *   tenant()->isSet()            → bool
     */
    function tenant(): TenantManager
    {
        return app(TenantManager::class);
    }
}
