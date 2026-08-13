<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('attendance_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained()->cascadeOnDelete();
        $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('title', 180);
        $table->timestamp('opens_at');
        $table->timestamp('late_after')->nullable();
        $table->timestamp('closes_at');
        $table->string('status', 20)->default('draft');
        $table->boolean('allow_manual')->default(true);
        $table->boolean('require_gps')->default(false);
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->unsignedInteger('radius_meters')->default(100);
        $table->boolean('require_dynamic_qr')->default(false);
        $table->unsignedInteger('qr_rotation_seconds')->default(30);
        $table->string('qr_secret', 96)->nullable();
        $table->boolean('require_selfie')->default(false);
        $table->timestamp('closed_at')->nullable();
        $table->timestamps();
        $table->index(['school_id','classroom_id','status']);
        $table->index(['opens_at','closes_at']);
    }); }
    public function down(): void { Schema::dropIfExists('attendance_sessions'); }
};
