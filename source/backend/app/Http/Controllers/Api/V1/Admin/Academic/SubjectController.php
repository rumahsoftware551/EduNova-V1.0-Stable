<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller; use App\Models\Subject; use Illuminate\Http\{JsonResponse,Request}; use Illuminate\Validation\Rule;
class SubjectController extends Controller
{
 public function index(Request $r): JsonResponse { return response()->json(['data'=>Subject::where('school_id',$r->user()->school_id)->orderBy('name')->get()]); }
 public function store(Request $r): JsonResponse { $d=$this->validated($r);$m=Subject::create([...$d,'school_id'=>$r->user()->school_id]);return response()->json(['data'=>$m],201); }
 public function update(Request $r,int $id): JsonResponse { $m=$this->find($r,$id);$m->update($this->validated($r,$id));return response()->json(['data'=>$m->fresh()]); }
 public function destroy(Request $r,int $id): JsonResponse { $this->find($r,$id)->delete();return response()->json(null,204); }
 private function find(Request $r,int $id): Subject { return Subject::where('school_id',$r->user()->school_id)->findOrFail($id); }
 private function validated(Request $r,?int $id=null): array { return $r->validate(['code'=>['required','string','max:30',Rule::unique('subjects')->where('school_id',$r->user()->school_id)->ignore($id)],'name'=>'required|string|max:150','category'=>'required|string|max:80','description'=>'nullable|string|max:1000','is_active'=>'sometimes|boolean']); }
}
