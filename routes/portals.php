<?php
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Portal\ParentPortalController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\StudentPortalController;
use App\Http\Controllers\SchoolLandingController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

Route::middleware([IdentifyTenant::class])->group(function () {
    Route::get('/impersonate/{token}', [ImpersonateController::class, 'handle'])->name('impersonate.handle');
    Route::get('/', [SchoolLandingController::class, 'index'])->name('school.home');

    Route::get('/login',   [PortalAuthController::class,'showLogin'])->name('portal.login');
    Route::post('/login',  [PortalAuthController::class,'login'])->name('portal.login.post');
    Route::post('/logout', [PortalAuthController::class,'logout'])->name('portal.logout');

    Route::prefix('parent')->middleware(['auth','portal.role:parent'])->group(function () {
        Route::get('/',                    [ParentPortalController::class,'dashboard'])->name('parent.dashboard');
        Route::get('/attendance/{student}',[ParentPortalController::class,'attendance'])->name('parent.attendance');
        Route::get('/results/{student}',   [ParentPortalController::class,'results'])->name('parent.results');
        Route::get('/fees/{student}',      [ParentPortalController::class,'fees'])->name('parent.fees');
        Route::get('/timetable/{student}', [ParentPortalController::class,'timetable'])->name('parent.timetable');
        Route::get('/announcements',       [ParentPortalController::class,'announcements'])->name('parent.announcements');
    });

    Route::prefix('student')->middleware(['auth','portal.role:student'])->group(function () {
        Route::get('/',             [StudentPortalController::class,'dashboard'])->name('student.dashboard');
        Route::get('/attendance',   [StudentPortalController::class,'attendance'])->name('student.attendance');
        Route::get('/results',      [StudentPortalController::class,'results'])->name('student.results');
        Route::get('/fees',         [StudentPortalController::class,'fees'])->name('student.fees');
        Route::get('/timetable',    [StudentPortalController::class,'timetable'])->name('student.timetable');
        Route::get('/announcements',[StudentPortalController::class,'announcements'])->name('student.announcements');
    });
});