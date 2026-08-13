<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('announcements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
        $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
        $table->string('title', 180);
        $table->text('body');
        $table->string('status', 20)->default('draft');
        $table->boolean('is_pinned')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
        $table->index(['classroom_id','status','published_at']);
    }); }
    public function down(): void { Schema::dropIfExists('announcements'); }
};
