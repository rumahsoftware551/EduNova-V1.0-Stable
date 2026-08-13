<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\{ClassEnrollment,ClassGroup,User}; use Illuminate\Http\{JsonResponse,Request};
class EnrollmentController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>ClassEnrollment::with(['classGroup:id,name,school_id','student:id,name,username,email,school_id'])->whereHas('classGroup',fn($q)=>$q->where('school_id',$r->user()->school_id))->latest()->get()]); }
 public function store(Request $r): JsonResponse { $d=$r->validate(['class_group_id'=>'required|integer','student_id'=>'required|integer','enrolled_at'=>'nullable|date','status'=>'nullable|in:active,completed,transferred']);ClassGroup::where('school_id',$r->user()->school_id)->findOrFail($d['class_group_id']);User::where('school_id',$r->user()->school_id)->where('role','student')->findOrFail($d['student_id']);$m=ClassEnrollment::updateOrCreate(['class_group_id'=>$d['class_group_id'],'student_id'=>$d['student_id']],['enrolled_at'=>$d['enrolled_at']??now()->toDateString(),'status'=>$d['status']??'active']);return response()->json(['data'=>$m->load(['classGroup:id,name','student:id,name,username,email'])],201); }
 public function destroy(Request $r,int $id): JsonResponse { $m=ClassEnrollment::whereHas('classGroup',fn($q)=>$q->where('school_id',$r->user()->school_id))->findOrFail($id);$m->delete();return response()->json(null,204); }
}
