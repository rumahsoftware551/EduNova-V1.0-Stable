<?php
namespace App\Http\Controllers\Api\V1\Teacher;
use App\Http\Controllers\Controller; use App\Models\Assignment; use App\Models\AssignmentSubmission; use App\Models\RubricScore; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class SubmissionReviewController extends Controller
{
    private function ownedAssignment(Request $request,int $id): Assignment { return Assignment::whereHas('classroom',fn($q)=>$q->where('school_id',$request->user()->school_id)->whereHas('teachingAssignment',fn($t)=>$t->where('teacher_id',$request->user()->id)))->findOrFail($id); }
    private function ownedSubmission(Request $request,int $id): AssignmentSubmission { return AssignmentSubmission::whereHas('assignment.classroom',fn($q)=>$q->where('school_id',$request->user()->school_id)->whereHas('teachingAssignment',fn($t)=>$t->where('teacher_id',$request->user()->id)))->findOrFail($id); }
    public function index(Request $request,int $assignmentId): JsonResponse
    {
        $assignment=$this->ownedAssignment($request,$assignmentId);
        $items=$assignment->submissions()->with(['student:id,name,username,subtitle','files'])->orderByRaw("case when status in ('submitted','late') then 0 else 1 end")->orderByDesc('submitted_at')->get();
        return response()->json(['data'=>$items]);
    }
    public function show(Request $request,int $id): JsonResponse
    {
        $item=$this->ownedSubmission($request,$id)->load(['student:id,name,username,subtitle','files','rubricScores.criterion','assignment.rubricCriteria','assignment.attachments','assignment.classroom.teachingAssignment.subject:id,code,name','assignment.classroom.teachingAssignment.classGroup:id,name']);
        return response()->json(['data'=>$item]);
    }
    public function grade(Request $request,int $id): JsonResponse
    {
        $submission=$this->ownedSubmission($request,$id)->load('assignment.rubricCriteria'); $assignment=$submission->assignment;
        $data=$request->validate(['score'=>['nullable','numeric','min:0'],'feedback'=>['nullable','string','max:20000'],'rubric_scores'=>['nullable','array'],'rubric_scores.*.criterion_id'=>['required','integer'],'rubric_scores.*.points'=>['required','numeric','min:0'],'rubric_scores.*.comment'=>['nullable','string','max:2000']]);
        if(!in_array($submission->status,['submitted','late','graded'],true)) return response()->json(['message'=>'Submission ini belum siap dinilai.'],422);
        DB::transaction(function()use($submission,$assignment,$data,$request){
            $score=$data['score']??null;
            if($assignment->rubricCriteria->isNotEmpty()){
                $allowed=$assignment->rubricCriteria->keyBy('id'); $score=0; RubricScore::where('submission_id',$submission->id)->delete();
                foreach(($data['rubric_scores']??[]) as $row){$criterion=$allowed->get((int)$row['criterion_id']);if(!$criterion)continue;$points=min((float)$row['points'],(float)$criterion->max_points);$score+=$points;RubricScore::create(['submission_id'=>$submission->id,'rubric_criterion_id'=>$criterion->id,'points'=>$points,'comment'=>$row['comment']??null]);}
            }
            if($score===null) $score=0; if((float)$score>(float)$assignment->max_score) abort(422,'Nilai melebihi nilai maksimum tugas.');
            $submission->update(['status'=>'graded','score'=>$score,'feedback'=>$data['feedback']??null,'graded_by'=>$request->user()->id,'graded_at'=>now(),'returned_at'=>null]);
        });
        return response()->json(['data'=>$submission->fresh()->load(['student:id,name,username','files','rubricScores.criterion'])]);
    }
    public function returnToStudent(Request $request,int $id): JsonResponse
    {
        $submission=$this->ownedSubmission($request,$id); if($submission->status!=='graded') return response()->json(['message'=>'Simpan nilai terlebih dahulu sebelum mengembalikan tugas.'],422);
        $submission->update(['status'=>'returned','returned_at'=>now()]); return response()->json(['data'=>$submission->fresh()]);
    }
}
