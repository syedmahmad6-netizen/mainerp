<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete()->unique();

            // daily = one record per student per day
            // period = separate record per subject per period (Phase 3)
            $table->enum('type', ['daily', 'period'])->default('daily');

            // SMS alert when attendance drops below this percentage
            $table->unsignedTinyInteger('low_threshold_percent')->default(75);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
