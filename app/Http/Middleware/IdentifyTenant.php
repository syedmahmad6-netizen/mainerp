<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * IdentifyTenant Middleware
 *
 * Runs on every request to a school subdomain (*.gnosis.ac.pk).
 * 1. Extracts the subdomain from the request host.
 * 2. Looks up the School record in the database.
 * 3. Injects the School into TenantManager (global singleton).
 * 4. All subsequent queries on tenant models are auto-scoped to this school.
 */
class IdentifyTenant
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host       = $request->getHost();
        $baseDomain = config('tenancy.base_domain');

        // If not a subdomain request, skip tenant resolution.
        // This covers the main domain: gnosis.ac.pk (Super Admin panel)
        if (! str_ends_with($host, '.' . $baseDomain)) {
            return $next($request);
        }

        // Extract subdomain — e.g., "demo.gnosis.ac.pk" → "demo"
        $subdomain = str_replace('.' . $baseDomain, '', $host);

        if (empty($subdomain)) {
            return $next($request);
        }

        // Find the school — must exist AND be active
        $school = School::where('subdomain', $subdomain)
                        ->where('is_active', true)
                        ->first();

        if (! $school) {
            abort(404, 'School portal not found or has been deactivated.');
        }

        // ✅ Set the tenant for this entire request
        $this->tenantManager->setSchool($school);

        return $next($request);
    }
}
