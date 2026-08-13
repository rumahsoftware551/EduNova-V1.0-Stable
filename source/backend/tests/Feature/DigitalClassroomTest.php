<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\Phase04AcademicSeeder;
use Database\Seeders\Phase05DigitalClassroomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_and_student_can_access_their_digital_classroom_endpoints(): void
    {
        $this->seed();
        $this->seed(Phase04AcademicSeeder::class);
        $this->seed(Phase05DigitalClassroomSeeder::class);

        $teacher = User::where('username', 'susanto')->firstOrFail();
        $student = User::where('username', 'andi.saputra')->firstOrFail();

        $this->actingAs($teacher)->getJson('/api/v1/teacher/classrooms')->assertOk();
        $this->actingAs($student)->getJson('/api/v1/student/classrooms')->assertOk();
        $this->actingAs($student)->getJson('/api/v1/teacher/classrooms')->assertForbidden();
    }
}
