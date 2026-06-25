<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // NULL for super_admin — they don't belong to a school
            $table->foreignId('school_id')
                  ->nullable()
                  ->constrained('schools')
                  ->cascadeOnDelete();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');

            $table->enum('role', [
                'super_admin',
                'principal',
                'vice_principal',
                'school_manager',
                'teacher',
                'accountant',
                'librarian',
                'student',
                'parent',
            ]);

            $table->boolean('is_active')->default(true);
            $table->string('profile_photo')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['school_id', 'role']);
            $table->index(['school_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
