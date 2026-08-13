<?php
namespace Database\Seeders;
use App\Models\{AcademicYear,ClassEnrollment,ClassGroup,Major,School,Semester,StudentProfile,Subject,TeacherProfile,TeachingAssignment,User};
use Illuminate\Database\Seeder;
class Phase04AcademicSeeder extends Seeder
{
 public function run(): void {
  $school=School::where('code','SMKN6-TJT')->firstOrFail();
  $year=AcademicYear::updateOrCreate(['school_id'=>$school->id,'name'=>'2026/2027'],['starts_on'=>'2026-07-13','ends_on'=>'2027-06-25','is_active'=>true]);
  $semester=Semester::updateOrCreate(['academic_year_id'=>$year->id,'number'=>1],['school_id'=>$school->id,'name'=>'Ganjil','starts_on'=>'2026-07-13','ends_on'=>'2026-12-18','is_active'=>true]);
  $major=Major::updateOrCreate(['school_id'=>$school->id,'code'=>'DKV'],['name'=>'Desain Komunikasi Visual','description'=>'Konsentrasi keahlian Desain Komunikasi Visual','is_active'=>true]);
  $teacher=User::where('school_id',$school->id)->where('username','susanto')->firstOrFail();
  $student=User::where('school_id',$school->id)->where('username','andi.saputra')->firstOrFail();
  TeacherProfile::updateOrCreate(['user_id'=>$teacher->id],['employee_no'=>'GTK-DKV-001','expertise'=>'Fotografi & Desain Komunikasi Visual','is_homeroom_eligible'=>true]);
  StudentProfile::updateOrCreate(['user_id'=>$student->id],['nis'=>'20260001','nisn'=>'0098765432','gender'=>'L','parent_name'=>'Orang Tua Andi']);
  $class=ClassGroup::updateOrCreate(['academic_year_id'=>$year->id,'name'=>'XI DKV 1'],['school_id'=>$school->id,'major_id'=>$major->id,'homeroom_teacher_id'=>$teacher->id,'grade_level'=>11,'capacity'=>36,'is_active'=>true]);
  foreach([['FOT-DIG','Fotografi Digital','kejuruan'],['DKV-XI','Desain Komunikasi Visual','kejuruan'],['TIP-MOD','Tipografi Modern','kejuruan']] as [$code,$name,$cat]) Subject::updateOrCreate(['school_id'=>$school->id,'code'=>$code],['name'=>$name,'category'=>$cat,'is_active'=>true]);
  $subject=Subject::where('school_id',$school->id)->where('code','FOT-DIG')->firstOrFail();
  ClassEnrollment::updateOrCreate(['class_group_id'=>$class->id,'student_id'=>$student->id],['enrolled_at'=>'2026-07-13','status'=>'active']);
  TeachingAssignment::updateOrCreate(['class_group_id'=>$class->id,'subject_id'=>$subject->id,'teacher_id'=>$teacher->id,'semester_id'=>$semester->id],['weekly_hours'=>6]);
 }
}
