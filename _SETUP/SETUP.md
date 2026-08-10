# Gnosis Education Systems — Phase 1 Setup Guide

## What's Inside This Package
All custom PHP, Blade, and config files for Phase 1 of Gnosis.
You install Laravel first, then copy these files over it.

---

## STEP 1 — Install Laravel & Filament
Run these on your local machine (requires PHP 8.2+ and Composer installed):

```bash
composer create-project laravel/laravel gnosis
cd gnosis
composer require filament/filament:"^3.2"
```

---

## STEP 2 — Delete Default Laravel Migrations
Laravel creates some migrations you don't need. Delete these files:

```
database/migrations/0001_01_01_000000_create_users_table.php
database/migrations/0001_01_01_000001_create_cache_table.php
database/migrations/0001_01_01_000002_create_jobs_table.php
```

---

## STEP 3 — Copy This Package Into Your Project
Copy everything from this folder INTO your gnosis/ project folder.
When asked to overwrite files, say YES to all.

Files being overwritten:
- bootstrap/app.php
- bootstrap/providers.php
- routes/web.php
- app/Models/User.php

---

## STEP 4 — Update composer.json
Open gnosis/composer.json and find the "autoload" section.
Add the "files" line so it looks like this:

```json
"autoload": {
    "files": [
        "app/helpers.php"
    ],
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
},
```

Then run:
```bash
composer dump-autoload
```

---

## STEP 5 — Configure Your .env File
Open gnosis/.env and update these values:

```env
APP_NAME="Gnosis Education Systems"
APP_URL=http://gnosis.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gnosis
DB_USERNAME=root
DB_PASSWORD=your_password_here

TENANT_BASE_DOMAIN=gnosis.test
SUPER_ADMIN_DOMAIN=gnosis.test
```

Note: Use gnosis.test for local development. Change to gnosis.ac.pk for production.

---

## STEP 6 — Create Database
Create a MySQL database named "gnosis" then run:

```bash
php artisan migrate
```

---

## STEP 7 — Create Your Super Admin Account
```bash
php artisan db:seed --class=SuperAdminSeeder
```

Login credentials:
- Email:    admin@gnosis.ac.pk
- Password: change-me-now

⚠️ Change the password immediately after first login!

---

## STEP 8 — Configure Local Subdomains (For Local Development)
Add these lines to your hosts file:

Windows: C:\Windows\System32\drivers\etc\hosts
Mac/Linux: /etc/hosts

```
127.0.0.1   gnosis.test
127.0.0.1   demo.gnosis.test
127.0.0.1   pilot.gnosis.test
```

---

## STEP 9 — Start the App
```bash
php artisan serve --host=gnosis.test --port=8000
```

Access your panels:
- Super Admin: http://gnosis.test:8000/super-admin
- School Panel: http://demo.gnosis.test:8000/admin (after creating a school)

---

## STEP 10 — Seed Pilot School Data (Optional)
After creating your father's school via the Super Admin panel:

1. Find the school's ID (it will be 1 for first school)
2. Add to .env:  SEED_SCHOOL_ID=1
3. Run: php artisan db:seed --class=DefaultClassesSeeder

This creates:
- Academic Year 2025-2026 (set as current)
- 13 Classes (Nursery to Grade 10)
- 11 Subjects (English, Urdu, Maths, etc.)

---

## Panels Summary

| Panel | URL | Who |
|-------|-----|-----|
| Super Admin | gnosis.test/super-admin | You (platform owner) |
| School Admin | school-name.gnosis.test/admin | Principal, Manager, Teachers |

---

## Troubleshooting

**"Class not found" errors:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

**Migrations failing:**
Make sure you deleted the 3 default Laravel migrations in Step 2.

**Subdomain not working locally:**
Make sure you added the subdomain to your hosts file (Step 8).

**Filament panel not loading:**
```bash
php artisan filament:assets
```
