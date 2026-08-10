<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_id')->nullable();
            $table->string('cnic', 15)->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'part_time', 'visiting'])->default('permanent');
            $table->enum('status', ['active', 'on_leave', 'resigned', 'terminated'])->default('active');
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['school_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
