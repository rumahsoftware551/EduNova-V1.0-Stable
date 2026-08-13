<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\AcademicYear; use Illuminate\Http\{JsonResponse,Request}; use Illuminate\Validation\Rule;
class AcademicYearController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>AcademicYear::where('school_id',$r->user()->school_id)->orderByDesc('starts_on')->get()]); }
 public function store(Request $r): JsonResponse { $d=$this->validated($r); if($d['is_active']??false) AcademicYear::where('school_id',$r->user()->school_id)->update(['is_active'=>false]); $m=AcademicYear::create([...$d,'school_id'=>$r->user()->school_id]); return response()->json(['data'=>$m],201); }
 public function update(Request $r,int $id): JsonResponse { $m=$this->find($r,$id);$d=$this->validated($r,$id);if($d['is_active']??false) AcademicYear::where('school_id',$r->user()->school_id)->whereKeyNot($id)->update(['is_active'=>false]);$m->update($d);return response()->json(['data'=>$m->fresh()]); }
 public function destroy(Request $r,int $id): JsonResponse { $this->find($r,$id)->delete(); return response()->json(null,204); }
 private function find(Request $r,int $id): AcademicYear { return AcademicYear::where('school_id',$r->user()->school_id)->findOrFail($id); }
 private function validated(Request $r,?int $id=null): array { return $r->validate(['name'=>['required','string','max:40',Rule::unique('academic_years')->where('school_id',$r->user()->school_id)->ignore($id)],'starts_on'=>'required|date','ends_on'=>'required|date|after:starts_on','is_active'=>'sometimes|boolean']); }
}
