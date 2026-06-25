<?php
namespace Database\Seeders;
use App\Models\School;
use App\Models\SmsTemplate;
use App\Support\TenantManager;
use Illuminate\Database\Seeder;
class DefaultSmsTemplatesSeeder extends Seeder {
    public function run(): void {
        $schoolId = env('SEED_SCHOOL_ID');
        if (!$schoolId) { $this->command->error('Set SEED_SCHOOL_ID in .env first.'); return; }
        $school = School::findOrFail($schoolId);
        app(TenantManager::class)->setSchool($school);
        $templates = [
            ['name'=>'Absent Alert','message_body'=>'Dear Parent, {student_name} was absent on {date}. Please contact {school_name} for details.','variables'=>['student_name','date','school_name']],
            ['name'=>'Fee Reminder','message_body'=>'Dear Parent, Rs. {amount} fee is due for {student_name} for {month}. Please pay before {due_date}. Contact {school_name}.','variables'=>['amount','student_name','month','due_date','school_name']],
            ['name'=>'Results Published','message_body'=>'Dear Parent, {exam_name} results for {student_name} are now available. Login to {school_name} portal to view.','variables'=>['exam_name','student_name','school_name']],
            ['name'=>'Low Attendance Alert','message_body'=>'Dear Parent, {student_name} attendance is {percentage}% this month. Minimum required is 75%. Please ensure regular attendance. — {school_name}','variables'=>['student_name','percentage','school_name']],
            ['name'=>'General Notice','message_body'=>'Dear Parent, {message}. For queries contact {school_name}.','variables'=>['message','school_name']],
        ];
        foreach ($templates as $t) { SmsTemplate::create($t); }
        $this->command->info('✅ 5 default SMS templates created.');
    }
}