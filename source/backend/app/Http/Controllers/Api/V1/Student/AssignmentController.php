<?php
namespace App\Http\Controllers\Api\V1\Student;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassEnrollment;
use App\Models\SubmissionFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class AssignmentController extends Controller
{
    private function classGroupIds(Request $request)
    {
        return ClassEnrollment::where('student_id',$request->user()->id)->where('status','active')->pluck('class_group_id');
    }
    private function accessible(Request $request)
    {
        $ids=$this->classGroupIds($request);
        return Assignment::query()->where('status','published')
            ->whereHas('classroom',fn($q)=>$q->where('school_id',$request->user()->school_id)->where('status','published')
                ->whereHas('teachingAssignment',fn($t)=>$t->whereIn('class_group_id',$ids)));
    }
    private function hydrateForStudent(Assignment $assignment,int $studentId): Assignment
    {
        $submission=$assignment->submissions()->where('student_id',$studentId)->with(['files','rubricScores.criterion'])->first();
        $assignment->setRelation('studentSubmission',$submission);
        $assignment->setAttribute('is_overdue',$assignment->due_at?now()->greaterThan($assignment->due_at):false);
        return $assignment;
    }
    public function index(Request $request): JsonResponse
    {
        $studentId=$request->user()->id;
        $items=$this->accessible($request)->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name,grade_level','classroom.teachingAssignment.teacher:id,name'])
            ->orderByRaw('due_at is null')->orderBy('due_at')->get();
        $items->each(fn($a)=>$this->hydrateForStudent($a,$studentId));
        return response()->json(['data'=>$items]);
    }
    public function show(Request $request,int $id): JsonResponse
    {
        $item=$this->accessible($request)->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name,grade_level','classroom.teachingAssignment.teacher:id,name','attachments','rubricCriteria'])->findOrFail($id);
        return response()->json(['data'=>$this->hydrateForStudent($item,$request->user()->id)]);
    }
    public function save(Request $request,int $id): JsonResponse
    {
        $assignment=$this->accessible($request)->findOrFail($id);
        if($assignment->opens_at && now()->lessThan($assignment->opens_at)) return response()->json(['message'=>'Tugas belum dibuka.'],422);
        if($assignment->status==='closed') return response()->json(['message'=>'Pengumpulan tugas sudah ditutup.'],422);
        $maxKb=max(1024,(int)$assignment->max_file_mb*1024);
        $data=$request->validate([
            'action'=>['required','in:draft,submit'],'text_answer'=>['nullable','string','max:50000'],'link_url'=>['nullable','url','max:2000'],
            'files'=>['nullable','array','max:5'],'files.*'=>['file','max:'.$maxKb],
        ]);
        $submission=AssignmentSubmission::firstOrNew(['assignment_id'=>$assignment->id,'student_id'=>$request->user()->id]);
        $existingStatus=$submission->exists?$submission->status:null;
        $pastDue=$assignment->due_at && now()->greaterThan($assignment->due_at);
        if($data['action']==='draft' && $pastDue) return response()->json(['message'=>'Deadline telah lewat. Draft tidak dapat disimpan lagi.'],422);
        if($data['action']==='draft' && in_array($existingStatus,['submitted','late'],true)) return response()->json(['message'=>'Submission sudah terkirim. Ubah jawaban lalu gunakan Resubmit untuk mengirim versi baru.'],422);
        if(in_array($existingStatus,['graded','returned'],true)) return response()->json(['message'=>'Tugas sudah dinilai dan tidak dapat dikirim ulang.'],422);
        if(in_array($existingStatus,['submitted','late'],true)){
            if(!$assignment->allow_resubmit) return response()->json(['message'=>'Guru tidak mengizinkan resubmit untuk tugas ini.'],422);
            if($pastDue) return response()->json(['message'=>'Resubmit hanya dapat dilakukan sebelum deadline.'],422);
        }
        DB::transaction(function()use($request,$assignment,$submission,$data,$pastDue,$existingStatus){
            $submission->fill(['text_answer'=>$assignment->allow_text?($data['text_answer']??null):null,'link_url'=>$assignment->allow_link?($data['link_url']??null):null]);
            if(!$submission->exists){$submission->status='draft';$submission->attempt_no=1;}
            $submission->save();
            if($assignment->allow_file){foreach(($request->file('files')??[]) as $file){$path=$file->store("assignments/{$assignment->id}/submissions/{$request->user()->id}",'public');$submission->files()->create(['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()]);}}
            if($data['action']==='submit'){
                $hasAnswer=($assignment->allow_text && filled($submission->text_answer))||($assignment->allow_link && filled($submission->link_url))||($assignment->allow_file && $submission->files()->exists());
                if(!$hasAnswer) abort(422,'Lengkapi minimal satu bentuk submission sebelum mengirim tugas.');
                if(in_array($existingStatus,['submitted','late'],true)) $submission->attempt_no=((int)$submission->attempt_no)+1;
                $submission->status=$pastDue?'late':'submitted';$submission->is_late=$pastDue;$submission->submitted_at=now();$submission->score=null;$submission->feedback=null;$submission->graded_by=null;$submission->graded_at=null;$submission->returned_at=null;$submission->save();
            }else{$submission->status='draft';$submission->save();}
        });
        return response()->json(['data'=>$submission->fresh()->load(['files','rubricScores.criterion'])]);
    }
    public function removeFile(Request $request,int $fileId): JsonResponse
    {
        $file=SubmissionFile::with('submission.assignment')->findOrFail($fileId);$submission=$file->submission;$assignment=$submission->assignment;
        abort_unless($submission->student_id===$request->user()->id,403); abort_unless($assignment->status==='published',422);
        if(in_array($submission->status,['graded','returned'],true)) return response()->json(['message'=>'File pada tugas yang sudah dinilai tidak dapat dihapus.'],422);
        if(in_array($submission->status,['submitted','late'],true) && (!$assignment->allow_resubmit || ($assignment->due_at && now()->greaterThan($assignment->due_at)))) return response()->json(['message'=>'File tidak dapat diubah setelah deadline.'],422);
        Storage::disk('public')->delete($file->file_path);$file->delete();return response()->json(null,204);
    }
}
