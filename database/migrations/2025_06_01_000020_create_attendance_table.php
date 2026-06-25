<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();

            // subject_id is null for daily attendance, filled for period-wise
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();

            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'leave'])->default('present');
            $table->string('remarks')->nullable();

            // Who last edited this record (for audit — Principal overrides)
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('edit_reason')->nullable();

            $table->timestamps();

            $table->index('school_id');
            $table->index(['school_id', 'date']);
            $table->index(['section_id', 'date']);

            // One record per student per day (for daily attendance)
            $table->unique(['student_id', 'date', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
