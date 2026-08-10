<?php
namespace App\Http\Controllers\Portal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class PortalAuthController extends Controller {
    public function showLogin() {
        if (auth()->check()) return $this->redirectByRole(auth()->user()->role);
        return view('portal.login', ['school' => tenant()->getSchool()]);
    }
    public function login(Request $request) {
        $request->validate(['email'=>'required|email','password'=>'required']);
        if (auth()->attempt($request->only('email','password'))) {
            $user = auth()->user();
            if ($user->school_id !== tenant()->getSchoolId()) { auth()->logout(); return back()->withErrors(['email'=>'Invalid credentials.']); }
            if (!$user->is_active) { auth()->logout(); return back()->withErrors(['email'=>'Your account is inactive. Contact the school.']); }
            $request->session()->regenerate();
            return $this->redirectByRole($user->role);
        }
        return back()->withErrors(['email'=>'Incorrect email or password.'])->withInput($request->only('email'));
    }
    public function logout(Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
    private function redirectByRole(string $role) {
        return match($role) {
            'parent'  => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default   => redirect('/admin'),
        };
    }
}
