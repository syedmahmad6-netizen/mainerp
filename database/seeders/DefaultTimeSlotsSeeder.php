<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\TimeSlot;
use App\Support\TenantManager;
use Illuminate\Database\Seeder;

class DefaultTimeSlotsSeeder extends Seeder
{
    /**
     * Seeds a standard Pakistani school day timetable (7 periods + break + lunch).
     *
     * BEFORE RUNNING:
     *   Set SEED_SCHOOL_ID=1 in .env
     *   Run: php artisan db:seed --class=DefaultTimeSlotsSeeder
     */
    public function run(): void
    {
        $schoolId = env('SEED_SCHOOL_ID');

        if (! $schoolId) {
            $this->command->error('Set SEED_SCHOOL_ID in .env first.');
            return;
        }

        $school = School::findOrFail($schoolId);
        app(TenantManager::class)->setSchool($school);

        $slots = [
            ['name' => 'Assembly',  'start_time' => '07:45:00', 'end_time' => '08:00:00', 'slot_order' => 1,  'is_break' => true],
            ['name' => 'Period 1',  'start_time' => '08:00:00', 'end_time' => '08:45:00', 'slot_order' => 2,  'is_break' => false],
            ['name' => 'Period 2',  'start_time' => '08:45:00', 'end_time' => '09:30:00', 'slot_order' => 3,  'is_break' => false],
            ['name' => 'Period 3',  'start_time' => '09:30:00', 'end_time' => '10:15:00', 'slot_order' => 4,  'is_break' => false],
            ['name' => 'Break',     'start_time' => '10:15:00', 'end_time' => '10:30:00', 'slot_order' => 5,  'is_break' => true],
            ['name' => 'Period 4',  'start_time' => '10:30:00', 'end_time' => '11:15:00', 'slot_order' => 6,  'is_break' => false],
            ['name' => 'Period 5',  'start_time' => '11:15:00', 'end_time' => '12:00:00', 'slot_order' => 7,  'is_break' => false],
            ['name' => 'Lunch',     'start_time' => '12:00:00', 'end_time' => '12:30:00', 'slot_order' => 8,  'is_break' => true],
            ['name' => 'Period 6',  'start_time' => '12:30:00', 'end_time' => '13:15:00', 'slot_order' => 9,  'is_break' => false],
            ['name' => 'Period 7',  'start_time' => '13:15:00', 'end_time' => '14:00:00', 'slot_order' => 10, 'is_break' => false],
        ];

        foreach ($slots as $slot) {
            TimeSlot::create($slot);
        }

        $this->command->info('✅ 10 time slots created (Assembly, 7 Periods, Break, Lunch)');
        $this->command->info('   Modify timings from Academics → Time Slots in the school panel.');
    }
}
