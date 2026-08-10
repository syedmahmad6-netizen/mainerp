<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('timetable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->timestamps();
            $table->index('school_id');
            $table->index(['section_id', 'academic_year_id']);
            $table->unique(['section_id', 'time_slot_id', 'day_of_week', 'academic_year_id'], 'timetable_slot_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('timetable'); }
};
