<?php
namespace App\Http\Controllers;
use App\Models\Announcement;

class SchoolLandingController extends Controller {
    public function index() {
        $school = tenant()->getSchool();
        // Show latest 5 public announcements on landing page
        $announcements = Announcement::whereIn('target_role',['all','parents','students'])
            ->where('is_archived',false)
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))
            ->latest('published_at')->take(5)->get();
        return view('public.school-home', compact('school','announcements'));
    }
}