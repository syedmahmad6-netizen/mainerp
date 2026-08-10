<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Support\TenantManager;
use Illuminate\Database\Seeder;

class DefaultClassesSeeder extends Seeder
{
    /**
     * Seeds default Pakistani school structure for a given school.
     *
     * BEFORE RUNNING:
     *   1. Create your school via the Super Admin panel
     *   2. Find its ID in the database (it will be 1 for the first school)
     *   3. Add SEED_SCHOOL_ID=1 to your .env file
     *   4. Run: php artisan db:seed --class=DefaultClassesSeeder
     */
    public function run(): void
    {
        $schoolId = env('SEED_SCHOOL_ID');

        if (! $schoolId) {
            $this->command->error('❌ Please set SEED_SCHOOL_ID in your .env file first.');
            $this->command->info('   Example: SEED_SCHOOL_ID=1');
            return;
        }

        $school = School::find($schoolId);

        if (! $school) {
            $this->command->error("❌ No school found with ID: {$schoolId}");
            return;
        }

        // Inject tenant context so BelongsToSchool trait works correctly
        app(TenantManager::class)->setSchool($school);

        $this->command->info("🏫 Seeding default structure for: {$school->name}");

        // ── Academic Year ─────────────────────────────────────────────────
        AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-04-01',
            'end_date'   => '2026-03-31',
            'is_current' => true,
        ]);
        $this->command->info('   ✅ Academic Year: 2025-2026 (set as current)');

        // ── Classes — Standard Pakistani School Structure ─────────────────
        $classes = [
            ['name' => 'Nursery',   'numeric_order' => 0,  'level' => 'pre_primary'],
            ['name' => 'KG',        'numeric_order' => 1,  'level' => 'pre_primary'],
            ['name' => 'Prep',      'numeric_order' => 2,  'level' => 'pre_primary'],
            ['name' => 'Grade 1',   'numeric_order' => 3,  'level' => 'primary'],
            ['name' => 'Grade 2',   'numeric_order' => 4,  'level' => 'primary'],
            ['name' => 'Grade 3',   'numeric_order' => 5,  'level' => 'primary'],
            ['name' => 'Grade 4',   'numeric_order' => 6,  'level' => 'primary'],
            ['name' => 'Grade 5',   'numeric_order' => 7,  'level' => 'primary'],
            ['name' => 'Grade 6',   'numeric_order' => 8,  'level' => 'middle'],
            ['name' => 'Grade 7',   'numeric_order' => 9,  'level' => 'middle'],
            ['name' => 'Grade 8',   'numeric_order' => 10, 'level' => 'middle'],
            ['name' => 'Grade 9',   'numeric_order' => 11, 'level' => 'secondary'],
            ['name' => 'Grade 10',  'numeric_order' => 12, 'level' => 'secondary'],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }
        $this->command->info('   ✅ 13 Classes created (Nursery → Grade 10)');

        // ── Subjects — Standard Pakistani Curriculum ──────────────────────
        $subjects = [
            ['name' => 'English',         'code' => 'ENG',  'is_core' => true,  'color' => '#4f46e5'],
            ['name' => 'Urdu',            'code' => 'URD',  'is_core' => true,  'color' => '#059669'],
            ['name' => 'Mathematics',     'code' => 'MATH', 'is_core' => true,  'color' => '#dc2626'],
            ['name' => 'Science',         'code' => 'SCI',  'is_core' => true,  'color' => '#0891b2'],
            ['name' => 'Social Studies',  'code' => 'SST',  'is_core' => true,  'color' => '#d97706'],
            ['name' => 'Islamiat',        'code' => 'ISL',  'is_core' => true,  'color' => '#16a34a'],
            ['name' => 'Pakistan Studies','code' => 'PAK',  'is_core' => true,  'color' => '#be123c'],
            ['name' => 'Computer',        'code' => 'COMP', 'is_core' => false, 'color' => '#7c3aed'],
            ['name' => 'Physics',         'code' => 'PHY',  'is_core' => false, 'color' => '#1d4ed8'],
            ['name' => 'Chemistry',       'code' => 'CHEM', 'is_core' => false, 'color' => '#9333ea'],
            ['name' => 'Biology',         'code' => 'BIO',  'is_core' => false, 'color' => '#15803d'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
        $this->command->info('   ✅ 11 Subjects created');

        $this->command->info('');
        $this->command->info("🎉 Default structure seeded successfully for: {$school->name}");
        $this->command->info('   Next step: Go to Sections and create your class sections (e.g. Grade 1-A, Grade 1-B)');
    }
}
