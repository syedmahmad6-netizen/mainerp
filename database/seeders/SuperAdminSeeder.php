<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'school_id'  => null,
            'name'       => 'Gnosis Super Admin',
            'email'      => 'admin@gnosis.ac.pk',   // ← Change this before going live
            'password'   => 'supergnosis21',         // ← Change this immediately after first login
            'role'       => 'super_admin',
            'is_active'  => true,
        ]);

        $this->command->info('✅ Super Admin created: admin@gnosis.ac.pk / supergnosis21');
        $this->command->warn('⚠️  Change the password immediately after first login!');
    }
}
