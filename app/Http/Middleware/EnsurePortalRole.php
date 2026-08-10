<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsurePortalRole {
    public function handle(Request $request, Closure $next, string $role): Response {
        if (!auth()->check()) return redirect()->route('portal.login');
        $user = auth()->user();
        if ($user->school_id !== tenant()->getSchoolId()) { auth()->logout(); return redirect()->route('portal.login')->withErrors(['email'=>'Access denied.']); }
        if (!$user->is_active) { auth()->logout(); return redirect()->route('portal.login')->withErrors(['email'=>'Account deactivated. Contact school.']); }
        if ($user->role !== $role) {
            if (in_array($user->role,['principal','teacher','school_manager','vice_principal','accountant'])) return redirect('/admin');
            auth()->logout(); return redirect()->route('portal.login')->withErrors(['email'=>'Access denied for this portal.']);
        }
        return $next($request);
    }
}
