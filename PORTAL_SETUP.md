# Portal Setup Instructions

## Step 1 — Register Middleware Alias
In `bootstrap/app.php`, inside `->withMiddleware()`:

```php
$middleware->alias([
    'tenant'      => \App\Http\Middleware\IdentifyTenant::class,
    'portal.role' => \App\Http\Middleware\EnsurePortalRole::class,
]);
```

## Step 2 — Add Portal Routes
In `bootstrap/app.php`, update `->withRouting()`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    then: function () {
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/portals.php'));
    },
    health: '/up',
)
```

## Step 3 — Copy Files
Copy all files from this package into your gnosis/ project.

## Step 4 — Clear Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## How Portals Work
- Login URL:  school.gnosis.ac.pk/login
- Parent URL: school.gnosis.ac.pk/parent
- Student URL: school.gnosis.ac.pk/student
- Admin URL:  school.gnosis.ac.pk/admin (for teachers/principal)

One login page handles all roles — system redirects automatically.
