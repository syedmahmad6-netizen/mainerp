<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: Table is "school_classes" not "classes" — "classes" is a PHP reserved word
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('numeric_order')->default(0);
            $table->enum('level', [
                'pre_primary',
                'primary',
                'middle',
                'secondary',
                'higher_secondary',
            ]);
            $table->timestamps();

            $table->index('school_id');
            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'numeric_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
