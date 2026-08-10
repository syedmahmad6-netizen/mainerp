<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('recipient_phone');
            $table->string('recipient_name')->nullable();
            $table->text('message');
            $table->enum('status', ['sent','failed','pending'])->default('pending');
            $table->enum('trigger_type', [
                'manual',       // Sent manually by principal
                'absent',       // Student marked absent
                'fee_reminder', // Fee overdue
                'result',       // Results published
                'low_attendance'// Attendance below threshold
            ])->default('manual');
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index('school_id');
            $table->index(['school_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('sms_logs'); }
};