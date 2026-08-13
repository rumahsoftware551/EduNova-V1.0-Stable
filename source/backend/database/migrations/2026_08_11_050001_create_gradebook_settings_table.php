<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gradebook_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('assignment_weight', 5, 2)->default(60);
            $table->decimal('quiz_weight', 5, 2)->default(40);
            $table->decimal('passing_grade', 5, 2)->default(75);
            $table->boolean('missing_as_zero')->default(false);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('gradebook_settings'); }
};
