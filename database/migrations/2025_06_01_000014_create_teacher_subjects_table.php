<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links a teacher to the subjects they are qualified to teach.
        // Used in timetable assignment and subject filtering.
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['teacher_profile_id', 'subject_id']); // No duplicate subject per teacher
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
