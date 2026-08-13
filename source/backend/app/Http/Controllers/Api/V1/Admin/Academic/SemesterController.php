<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\{AcademicYear,Semester}; use Illuminate\Http\{JsonResponse,Request};
class SemesterController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>Semester::with('academicYear:id,name')->where('school_id',$r->user()->school_id)->orderByDesc('starts_on')->get()]); }
 public function store(Request $r): JsonResponse { $d=$this->validated($r);$this->year($r,$d['academic_year_id']);if($d['is_active']??false) Semester::where('school_id',$r->user()->school_id)->update(['is_active'=>false]);$m=Semester::create([...$d,'school_id'=>$r->user()->school_id]);return response()->json(['data'=>$m->load('academicYear:id,name')],201); }
 public function update(Request $r,int $id): JsonResponse { $m=$this->find($r,$id);$d=$this->validated($r);$this->year($r,$d['academic_year_id']);if($d['is_active']??false) Semester::where('school_id',$r->user()->school_id)->whereKeyNot($id)->update(['is_active'=>false]);$m->update($d);return response()->json(['data'=>$m->fresh()->load('academicYear:id,name')]); }
 public function destroy(Request $r,int $id): JsonResponse { $this->find($r,$id)->delete();return response()->json(null,204); }
 private function find(Request $r,int $id): Semester { return Semester::where('school_id',$r->user()->school_id)->findOrFail($id); }
 private function year(Request $r,int $id): AcademicYear { return AcademicYear::where('school_id',$r->user()->school_id)->findOrFail($id); }
 private function validated(Request $r): array { return $r->validate(['academic_year_id'=>'required|integer','name'=>'required|string|max:50','number'=>'required|integer|in:1,2','starts_on'=>'required|date','ends_on'=>'required|date|after:starts_on','is_active'=>'sometimes|boolean']); }
}
