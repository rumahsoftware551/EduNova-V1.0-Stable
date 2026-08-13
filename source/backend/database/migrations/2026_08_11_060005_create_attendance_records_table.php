<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('attendance_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
        $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
        $table->string('status', 24)->default('present');
        $table->string('method', 40)->default('manual');
        $table->timestamp('checked_in_at')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->decimal('distance_meters', 10, 2)->nullable();
        $table->decimal('gps_accuracy', 10, 2)->nullable();
        $table->string('selfie_path')->nullable();
        $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        $table->text('note')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
        $table->unique(['attendance_session_id','student_id'],'attendance_session_student_unique');
        $table->index(['student_id','status','checked_in_at']);
    }); }
    public function down(): void { Schema::dropIfExists('attendance_records'); }
};
