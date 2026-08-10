<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->foreignId('school_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('schools')
                  ->cascadeOnDelete();
        });

        // Fill in school_id for any existing rows based on their related exam
        DB::statement('
            UPDATE exam_schedules
            JOIN exams ON exams.id = exam_schedules.exam_id
            SET exam_schedules.school_id = exams.school_id
        ');
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};