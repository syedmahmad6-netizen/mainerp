<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('attachment')->nullable();
            $table->boolean('allow_submission')->default(true);
            $table->enum('status', ['active','closed'])->default('active');
            $table->timestamps();
            $table->index('school_id');
            $table->index(['section_id','subject_id']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('file')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['submitted','late','graded'])->default('submitted');
            $table->decimal('marks', 5, 2)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->unique(['assignment_id','student_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};