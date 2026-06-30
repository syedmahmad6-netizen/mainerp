<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('exam_date')->nullable();
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->unsignedSmallInteger('passing_marks')->default(40);
            $table->timestamps();
            $table->unique(['exam_id', 'school_class_id', 'subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_schedules'); }
};