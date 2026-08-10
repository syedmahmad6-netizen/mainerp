<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->decimal('obtained_marks', 6, 2)->default(0);
            $table->unsignedSmallInteger('total_marks');
            $table->string('grade', 5)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_pass')->default(false);
            $table->string('remarks')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('school_id');
            $table->index(['exam_id', 'student_id']);
            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_results'); }
};