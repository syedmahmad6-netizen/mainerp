<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('admission_number')->nullable();
            $table->string('roll_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('b_form_number', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->date('admission_date')->nullable();
            $table->enum('status', ['active', 'graduated', 'transferred', 'withdrawn'])->default('active');
            // FK to sections added after sections table is created (migration 09)
            $table->unsignedBigInteger('current_section_id')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['school_id', 'admission_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
