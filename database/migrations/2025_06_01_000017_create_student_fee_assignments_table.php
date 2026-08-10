<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links a fee structure to a specific student.
        // Allows per-student discounts (sibling, scholarship, etc.)
        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            // Discount applied to this student for this fee
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->string('discount_reason')->nullable(); // "Sibling", "Scholarship", "Staff Child"

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['student_profile_id', 'fee_structure_id', 'academic_year_id'], 'student_fee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_assignments');
    }
};
