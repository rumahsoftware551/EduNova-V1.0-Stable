<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
            $table->unique(['classroom_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
