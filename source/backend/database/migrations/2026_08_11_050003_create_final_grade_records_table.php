<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('final_grade_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('assignment_average', 6, 2)->nullable();
            $table->decimal('quiz_average', 6, 2)->nullable();
            $table->decimal('adjustment', 6, 2)->default(0);
            $table->decimal('final_score', 6, 2);
            $table->string('letter_grade', 3)->nullable();
            $table->string('result_status', 20)->default('incomplete');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at');
            $table->timestamps();
            $table->unique(['classroom_id', 'student_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('final_grade_records'); }
};
