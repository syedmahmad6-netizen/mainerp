<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->enum('type', ['general', 'urgent', 'holiday', 'event', 'exam_schedule'])->default('general');
            // Who sees this: all / parents / students / teachers / staff
            $table->enum('target_role', ['all', 'parents', 'students', 'teachers', 'staff'])->default('all');
            // null = school-wide; set to target a specific section
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('attachment')->nullable();   // file path
            $table->boolean('send_sms')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->index('school_id');
            $table->index(['school_id', 'target_role']);
        });
    }
    public function down(): void { Schema::dropIfExists('announcements'); }
};