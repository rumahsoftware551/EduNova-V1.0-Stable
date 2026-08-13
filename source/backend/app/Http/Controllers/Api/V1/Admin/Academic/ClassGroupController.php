<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\{AcademicYear,ClassGroup,Major,User}; use Illuminate\Http\{JsonResponse,Request};
class ClassGroupController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>ClassGroup::with(['academicYear:id,name','major:id,code,name','homeroomTeacher:id,name'])->where('school_id',$r->user()->school_id)->orderBy('grade_level')->orderBy('name')->get()]); }
 public function store(Request $r): JsonResponse { $d=$this->validated($r);$this->assertRefs($r,$d);$m=ClassGroup::create([...$d,'school_id'=>$r->user()->school_id]);return response()->json(['data'=>$m->load(['academicYear:id,name','major:id,code,name','homeroomTeacher:id,name'])],201); }
 public function update(Request $r,int $id): JsonResponse { $m=$this->find($r,$id);$d=$this->validated($r);$this->assertRefs($r,$d);$m->update($d);return response()->json(['data'=>$m->fresh()->load(['academicYear:id,name','major:id,code,name','homeroomTeacher:id,name'])]); }
 public function destroy(Request $r,int $id): JsonResponse { $this->find($r,$id)->delete();return response()->json(null,204); }
 private function find(Request $r,int $id): ClassGroup { return ClassGroup::where('school_id',$r->user()->school_id)->findOrFail($id); }
 private function validated(Request $r): array { return $r->validate(['academic_year_id'=>'required|integer','major_id'=>'nullable|integer','homeroom_teacher_id'=>'nullable|integer','name'=>'required|string|max:120','grade_level'=>'required|integer|min:1|max:13','capacity'=>'required|integer|min:1|max:100','is_active'=>'sometimes|boolean']); }
 private function assertRefs(Request $r,array $d): void { AcademicYear::where('school_id',$r->user()->school_id)->findOrFail($d['academic_year_id']); if(!empty($d['major_id'])) Major::where('school_id',$r->user()->school_id)->findOrFail($d['major_id']); if(!empty($d['homeroom_teacher_id'])) User::where('school_id',$r->user()->school_id)->where('role','teacher')->findOrFail($d['homeroom_teacher_id']); }
}
