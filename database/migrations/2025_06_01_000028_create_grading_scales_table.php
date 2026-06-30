<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('grade', 5);
            $table->decimal('min_percent', 5, 2);
            $table->decimal('max_percent', 5, 2);
            $table->decimal('gpa', 3, 2)->default(0);
            $table->timestamps();
            $table->index('school_id');
            $table->unique(['school_id', 'grade']);
        });
    }
    public function down(): void { Schema::dropIfExists('grading_scales'); }
};