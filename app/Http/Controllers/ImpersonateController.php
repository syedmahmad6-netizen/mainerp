<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /**
     * STEP 1 — Called from Super Admin panel.
     * Generates a short-lived token, stores in cache,
     * then redirects to the school's subdomain.
     */
    public function initiate(School $school): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'super_admin', 403);

        // Find the most privileged user in that school
        $targetUser = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('role', ['principal', 'school_manager', 'vice_principal'])
            ->where('is_active', true)
            ->orderByRaw("FIELD(role, 'principal', 'school_manager', 'vice_principal')")
            ->first();

        if (! $targetUser) {
            return back()->with(
                'error',
                'No admin account found for this school. Create a Principal first.'
            );
        }

        // Store a one-time, 5-minute token in cache
        $token = Str::random(40);
        Cache::put("gnosis_impersonate:{$token}", [
            'user_id'           => $targetUser->id,
            'school_id'         => $school->id,
            'initiated_by_id'   => auth()->id(),
            'initiated_by_name' => auth()->user()->name,
        ], now()->addMinutes(5));

        $schoolDomain = 'https://' . $school->subdomain . '.' . config('tenancy.base_domain');

        return redirect("{$schoolDomain}/impersonate/{$token}");
    }

    /**
     * STEP 2 — Called on the SCHOOL subdomain.
     * Validates token, logs in the target user,
     * sets session flag for the impersonation banner.
     */
    public function handle(string $token): RedirectResponse
    {
        $data = Cache::get("gnosis_impersonate:{$token}");

        if (! $data) {
            abort(403, 'This access link has expired or already been used. Please generate a new one.');
        }

        // Ensure token is for THIS school (prevents cross-school reuse)
        if ($data['school_id'] !== tenant()->getSchoolId()) {
            Cache::forget("gnosis_impersonate:{$token}");
            abort(403, 'Access denied — school mismatch.');
        }

        Cache::forget("gnosis_impersonate:{$token}"); // One-time use

        $user = User::withoutGlobalScopes()->findOrFail($data['user_id']);
        Auth::login($user);

        session([
            'gnosis_impersonating'       => true,
            'gnosis_impersonated_school' => tenant()->getSchool()->name,
            'gnosis_initiated_by'        => $data['initiated_by_name'],
            'gnosis_return_url'          => 'https://' . config('tenancy.super_admin_domain') . '/super-admin',
        ]);

        return redirect('/admin');
    }
}
