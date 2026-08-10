<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('cnic', 15)->nullable();
            $table->string('occupation')->nullable();
            $table->string('relationship', 50)->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->index('school_id');
        });

        // One parent can have multiple children, one student can have multiple parents
        Schema::create('student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('parent_profile_id')->constrained('parent_profiles')->cascadeOnDelete();
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamps();

            $table->unique(['student_profile_id', 'parent_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parent');
        Schema::dropIfExists('parent_profiles');
    }
};
