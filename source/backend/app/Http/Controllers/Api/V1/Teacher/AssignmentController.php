<?php
namespace App\Http\Controllers\Api\V1\Teacher;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class AssignmentController extends Controller
{
    private function ownedClassroom(Request $request, int $id): Classroom
    {
        return Classroom::query()->where('school_id',$request->user()->school_id)
            ->whereHas('teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))->findOrFail($id);
    }
    private function owned(Request $request)
    {
        return Assignment::query()->whereHas('classroom',fn($q)=>$q->where('school_id',$request->user()->school_id)
            ->whereHas('teachingAssignment',fn($t)=>$t->where('teacher_id',$request->user()->id)));
    }
    private function baseRelations(): array { return ['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name,grade_level','attachments','rubricCriteria']; }
    public function index(Request $request): JsonResponse
    {
        $items=$this->owned($request)->with($this->baseRelations())
            ->withCount(['submissions','submissions as pending_review_count'=>fn($q)=>$q->whereIn('status',['submitted','late'])])
            ->orderByRaw('due_at is null')->orderBy('due_at')->latest('id')->get();
        return response()->json(['data'=>$items]);
    }
    public function store(Request $request, int $classroomId): JsonResponse
    {
        $classroom=$this->ownedClassroom($request,$classroomId);
        $data=$request->validate([
            'title'=>['required','string','max:180'],'instructions'=>['nullable','string','max:30000'],'opens_at'=>['nullable','date'],
            'due_at'=>['nullable','date'],'max_score'=>['nullable','numeric','min:1','max:10000'],'allow_text'=>['nullable','boolean'],
            'allow_link'=>['nullable','boolean'],'allow_file'=>['nullable','boolean'],'allow_resubmit'=>['nullable','boolean'],'max_file_mb'=>['nullable','integer','min:1','max:64'],
        ]);
        $assignment=$classroom->assignments()->create([...$data,'created_by'=>$request->user()->id,'status'=>'draft']);
        return response()->json(['data'=>$assignment->load($this->baseRelations())],201);
    }
    public function show(Request $request, int $id): JsonResponse
    {
        $item=$this->owned($request)->with($this->baseRelations())
            ->withCount(['submissions','submissions as pending_review_count'=>fn($q)=>$q->whereIn('status',['submitted','late'])])->findOrFail($id);
        return response()->json(['data'=>$item]);
    }
    public function update(Request $request, int $id): JsonResponse
    {
        $item=$this->owned($request)->findOrFail($id);
        $data=$request->validate([
            'title'=>['sometimes','required','string','max:180'],'instructions'=>['nullable','string','max:30000'],'opens_at'=>['nullable','date'],
            'due_at'=>['nullable','date'],'max_score'=>['nullable','numeric','min:1','max:10000'],'allow_text'=>['nullable','boolean'],
            'allow_link'=>['nullable','boolean'],'allow_file'=>['nullable','boolean'],'allow_resubmit'=>['nullable','boolean'],'max_file_mb'=>['nullable','integer','min:1','max:64'],
        ]);
        $item->update($data); return response()->json(['data'=>$item->fresh()->load($this->baseRelations())]);
    }
    public function publish(Request $request, int $id): JsonResponse
    {
        $item=$this->owned($request)->findOrFail($id); $data=$request->validate(['published'=>['required','boolean']]);
        if($data['published'] && !$item->allow_text && !$item->allow_link && !$item->allow_file) return response()->json(['message'=>'Aktifkan minimal satu metode pengumpulan tugas.'],422);
        $item->update(['status'=>$data['published']?'published':'draft','published_at'=>$data['published']?now():null]);
        return response()->json(['data'=>$item->fresh()]);
    }
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item=$this->owned($request)->with(['attachments','submissions.files'])->findOrFail($id);
        foreach($item->attachments as $file) Storage::disk('public')->delete($file->file_path);
        foreach($item->submissions as $submission) foreach($submission->files as $file) Storage::disk('public')->delete($file->file_path);
        $item->delete(); return response()->json(null,204);
    }
    public function addAttachment(Request $request, int $id): JsonResponse
    {
        $item=$this->owned($request)->findOrFail($id);
        $data=$request->validate(['file'=>['required','file','max:65536']]); $file=$data['file'];
        $path=$file->store("assignments/{$item->id}/attachments",'public');
        $record=$item->attachments()->create(['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()]);
        return response()->json(['data'=>$record],201);
    }
    public function removeAttachment(Request $request, int $attachmentId): JsonResponse
    {
        $attachment=AssignmentAttachment::with('assignment')->findOrFail($attachmentId);
        $this->owned($request)->findOrFail($attachment->assignment_id);
        Storage::disk('public')->delete($attachment->file_path); $attachment->delete(); return response()->json(null,204);
    }
}
