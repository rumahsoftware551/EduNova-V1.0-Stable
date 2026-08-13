<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\{ClassGroup,Semester,Subject,TeachingAssignment,User}; use Illuminate\Http\{JsonResponse,Request};
class TeachingAssignmentController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>TeachingAssignment::with(['classGroup:id,name,school_id','subject:id,name,code,school_id','teacher:id,name,school_id','semester:id,name,school_id'])->whereHas('classGroup',fn($q)=>$q->where('school_id',$r->user()->school_id))->latest()->get()]); }
 public function store(Request $r): JsonResponse { $d=$r->validate(['class_group_id'=>'required|integer','subject_id'=>'required|integer','teacher_id'=>'required|integer','semester_id'=>'required|integer','weekly_hours'=>'required|integer|min:1|max:20']);$sid=$r->user()->school_id;ClassGroup::where('school_id',$sid)->findOrFail($d['class_group_id']);Subject::where('school_id',$sid)->findOrFail($d['subject_id']);User::where('school_id',$sid)->where('role','teacher')->findOrFail($d['teacher_id']);Semester::where('school_id',$sid)->findOrFail($d['semester_id']);$m=TeachingAssignment::updateOrCreate(['class_group_id'=>$d['class_group_id'],'subject_id'=>$d['subject_id'],'teacher_id'=>$d['teacher_id'],'semester_id'=>$d['semester_id']],['weekly_hours'=>$d['weekly_hours']]);return response()->json(['data'=>$m->load(['classGroup:id,name','subject:id,name,code','teacher:id,name','semester:id,name'])],201); }
 public function destroy(Request $r,int $id): JsonResponse { $m=TeachingAssignment::whereHas('classGroup',fn($q)=>$q->where('school_id',$r->user()->school_id))->findOrFail($id);$m->delete();return response()->json(null,204); }
}
