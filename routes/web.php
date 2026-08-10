<?php

use App\Http\Controllers\ImpersonateController;
use Illuminate\Support\Facades\Route;

Route::domain(config('tenancy.base_domain'))->group(function () {
    Route::get('/', fn () => view('welcome'))->name('home');
});

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/super-admin/impersonate/{school}',
        [ImpersonateController::class, 'initiate']
    )->name('super-admin.impersonate');
});