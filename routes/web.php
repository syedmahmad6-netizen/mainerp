<?php

use App\Http\Controllers\ImpersonateController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Main Domain Routes (gnosis.ac.pk)
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'))->name('home');

/*
|--------------------------------------------------------------------------
| Super Admin Initiates Impersonation
|--------------------------------------------------------------------------
| Only authenticated Super Admin can hit this.
| Generates a one-time token and redirects to the school subdomain.
*/
Route::middleware(['auth'])->group(function () {
    Route::get(
        '/super-admin/impersonate/{school}',
        [ImpersonateController::class, 'initiate']
    )->name('super-admin.impersonate');
});

/*
|--------------------------------------------------------------------------
| School Subdomain Accepts Impersonation Token
|--------------------------------------------------------------------------
| Runs on school subdomain — IdentifyTenant fires first to set school context.
*/
Route::middleware([IdentifyTenant::class])->group(function () {
    Route::get(
        '/impersonate/{token}',
        [ImpersonateController::class, 'handle']
    )->name('school.impersonate.handle');
});
