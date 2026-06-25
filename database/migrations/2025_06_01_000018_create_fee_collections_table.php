<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every fee record lives here — one row per student per fee type per month.
        // Status starts as "pending", becomes "paid" when payment is recorded.
        Schema::create('fee_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            // Which month this fee covers (stored as first day of month: 2025-06-01)
            $table->date('fee_month');

            // Amounts
            $table->decimal('amount_due', 10, 2);     // Original fee amount
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('fine', 10, 2)->default(0); // Late payment fine
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0); // amount_due - discount - amount_paid

            // Payment details (filled when payment is recorded)
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->string('receipt_no')->nullable();
            $table->enum('payment_method', [
                'cash', 'cheque', 'bank_transfer', 'jazzcash', 'easypaisa'
            ])->nullable();

            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['school_id', 'student_profile_id']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'fee_month']);
            $table->unique(['student_profile_id', 'fee_type_id', 'fee_month']); // No duplicate records
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_collections');
    }
};
