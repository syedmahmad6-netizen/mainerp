<?php

/*
|--------------------------------------------------------------------------
| ADD THESE ROUTES to routes/portals.php
|--------------------------------------------------------------------------
| Add the school public landing page route inside the
| Route::middleware([IdentifyTenant::class]) group.
| This makes school.gnosis.ac.pk show the school homepage.
*/

// Add this INSIDE the existing IdentifyTenant middleware group in portals.php:
Route::get('/', [\App\Http\Controllers\SchoolLandingController::class, 'index'])
    ->name('school.home');

/*
|--------------------------------------------------------------------------
| UPDATE routes/web.php
|--------------------------------------------------------------------------
| Replace the existing root route with the Gnosis welcome page:
*/

// The default Route::get('/') in web.php already returns view('welcome')
// Just replace resources/views/welcome.blade.php with the new file.
// NO code change needed in web.php.
*/