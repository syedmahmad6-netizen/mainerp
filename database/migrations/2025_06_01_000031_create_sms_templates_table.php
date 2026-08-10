<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->text('message_body');
            $table->json('variables')->nullable(); // e.g. ["student_name","amount","date"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('school_id');
            $table->unique(['school_id','name']);
        });
    }
    public function down(): void { Schema::dropIfExists('sms_templates'); }
};