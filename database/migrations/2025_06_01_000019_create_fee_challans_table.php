<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A challan is a printed bank deposit slip given to parents.
        // One challan can cover multiple fee types for a student.
        Schema::create('fee_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            $table->string('challan_no');              // GNO-CHN-2025-0001
            $table->date('fee_month');                 // Which month this challan is for
            $table->date('due_date');                  // Pay by this date
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['school_id', 'challan_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_challans');
    }
};
