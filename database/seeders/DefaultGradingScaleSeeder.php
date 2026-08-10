<?php
namespace Database\Seeders;
use App\Models\GradingScale;
use App\Models\School;
use App\Support\TenantManager;
use Illuminate\Database\Seeder;
class DefaultGradingScaleSeeder extends Seeder {
    public function run(): void {
        $schoolId=env("SEED_SCHOOL_ID");
        if(!$schoolId){$this->command->error("Set SEED_SCHOOL_ID in .env first.");return;}
        $school=School::findOrFail($schoolId);
        app(TenantManager::class)->setSchool($school);
        $scales=[
            ["grade"=>"A1","min_percent"=>90,"max_percent"=>100,"gpa"=>4.0],
            ["grade"=>"A", "min_percent"=>80,"max_percent"=>89, "gpa"=>3.7],
            ["grade"=>"B", "min_percent"=>70,"max_percent"=>79, "gpa"=>3.0],
            ["grade"=>"C", "min_percent"=>60,"max_percent"=>69, "gpa"=>2.3],
            ["grade"=>"D", "min_percent"=>50,"max_percent"=>59, "gpa"=>1.7],
            ["grade"=>"E", "min_percent"=>40,"max_percent"=>49, "gpa"=>1.0],
            ["grade"=>"F", "min_percent"=>0, "max_percent"=>39, "gpa"=>0.0],
        ];
        foreach($scales as $scale) GradingScale::create($scale);
        $this->command->info("Pakistan Matric grading scale seeded (A1/A/B/C/D/E/F)");
    }
}