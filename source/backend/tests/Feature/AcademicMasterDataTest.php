<?php
namespace Tests\Feature;
use App\Models\{School,User}; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class AcademicMasterDataTest extends TestCase
{
 use RefreshDatabase;
 public function test_admin_can_manage_subjects_and_student_cannot(): void {
  $school=School::create(['name'=>'Test School','code'=>'TEST','slug'=>'test','timezone'=>'Asia/Jakarta','is_active'=>true]);
  $admin=User::factory()->create(['school_id'=>$school->id,'role'=>'admin','status'=>'active','username'=>'admin-test']);
  $student=User::factory()->create(['school_id'=>$school->id,'role'=>'student','status'=>'active','username'=>'student-test']);
  $this->actingAs($admin)->postJson('/api/v1/admin/academic/subjects',['code'=>'MAT','name'=>'Matematika','category'=>'umum','is_active'=>true])->assertCreated();
  $this->actingAs($student)->getJson('/api/v1/admin/academic/subjects')->assertForbidden();
 }
}
