<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\AssignmentAttachment;
use App\Models\ClassEnrollment;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AssignmentFileController extends Controller
{
    public function __invoke(Request $request,string $kind,int $id): StreamedResponse
    {
        $user=$request->user();$allowed=false;$path=null;$name=null;$mime=null;
        if($kind==='attachment'){
            $file=AssignmentAttachment::with('assignment.classroom.teachingAssignment')->findOrFail($id);$assignment=$file->assignment;$classroom=$assignment->classroom;$ta=$classroom->teachingAssignment;
            if($user->role==='teacher')$allowed=$ta->teacher_id===$user->id&&$classroom->school_id===$user->school_id;
            elseif($user->role==='student')$allowed=$assignment->status==='published'&&$classroom->status==='published'&&ClassEnrollment::where('student_id',$user->id)->where('class_group_id',$ta->class_group_id)->where('status','active')->exists();
            elseif($user->role==='admin')$allowed=$classroom->school_id===$user->school_id;
            [$path,$name,$mime]=[$file->file_path,$file->original_name,$file->mime_type];
        }elseif($kind==='submission'){
            $file=SubmissionFile::with('submission.assignment.classroom.teachingAssignment')->findOrFail($id);$submission=$file->submission;$classroom=$submission->assignment->classroom;$ta=$classroom->teachingAssignment;
            if($user->role==='teacher')$allowed=$ta->teacher_id===$user->id&&$classroom->school_id===$user->school_id;
            elseif($user->role==='student')$allowed=$submission->student_id===$user->id;
            elseif($user->role==='admin')$allowed=$classroom->school_id===$user->school_id;
            [$path,$name,$mime]=[$file->file_path,$file->original_name,$file->mime_type];
        }else abort(404);
        abort_unless($allowed,403);abort_unless($path&&Storage::disk('public')->exists($path),404);
        return Storage::disk('public')->response($path,$name,['Content-Type'=>$mime?:'application/octet-stream']);
    }
}
