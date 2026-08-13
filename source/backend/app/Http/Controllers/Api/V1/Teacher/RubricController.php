<?php
namespace App\Http\Controllers\Api\V1\Teacher;
use App\Http\Controllers\Controller; use App\Models\Assignment; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class RubricController extends Controller
{
    private function owned(Request $request,int $id): Assignment { return Assignment::whereHas('classroom',fn($q)=>$q->where('school_id',$request->user()->school_id)->whereHas('teachingAssignment',fn($t)=>$t->where('teacher_id',$request->user()->id)))->findOrFail($id); }
    public function sync(Request $request,int $id): JsonResponse
    {
        $assignment=$this->owned($request,$id);
        $data=$request->validate(['criteria'=>['array','max:20'],'criteria.*.title'=>['required','string','max:160'],'criteria.*.description'=>['nullable','string','max:1000'],'criteria.*.max_points'=>['required','numeric','min:0.01','max:10000']]);
        DB::transaction(function()use($assignment,$data){$assignment->rubricCriteria()->delete();$total=0;foreach(($data['criteria']??[]) as $i=>$c){$total+=(float)$c['max_points'];$assignment->rubricCriteria()->create([...$c,'position'=>$i+1]);}if($total>0)$assignment->update(['max_score'=>$total]);});
        return response()->json(['data'=>$assignment->fresh()->load('rubricCriteria')]);
    }
}
