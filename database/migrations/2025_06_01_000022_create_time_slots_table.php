<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('slot_order');
            $table->boolean('is_break')->default(false);
            $table->timestamps();
            $table->index('school_id');
            $table->unique(['school_id', 'slot_order']);
        });
    }
    public function down(): void { Schema::dropIfExists('time_slots'); }
};
