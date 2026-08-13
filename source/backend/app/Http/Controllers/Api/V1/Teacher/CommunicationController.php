<?php
namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\DiscussionPost;
use App\Services\EduNovaNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function __construct(private EduNovaNotificationService $notifications) {}

    private function owned(Request $request, int $classroomId): Classroom
    {
        return Classroom::query()
            ->where('school_id',$request->user()->school_id)
            ->whereHas('teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])
            ->findOrFail($classroomId);
    }

    public function classrooms(Request $request): JsonResponse
    {
        $items = Classroom::query()->where('school_id',$request->user()->school_id)
            ->whereHas('teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])
            ->orderBy('title')->get();
        return response()->json(['data'=>$items]);
    }

    public function announcements(Request $request, int $classroomId): JsonResponse
    {
        $this->owned($request,$classroomId);
        $items = Announcement::where('classroom_id',$classroomId)->with('author:id,name,role')->orderByDesc('is_pinned')->latest()->get();
        return response()->json(['data'=>$items]);
    }

    public function storeAnnouncement(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->owned($request,$classroomId);
        $data=$request->validate(['title'=>['required','string','max:180'],'body'=>['required','string','max:10000'],'is_pinned'=>['sometimes','boolean'],'publish'=>['sometimes','boolean']]);
        $publish=(bool)($data['publish']??false);
        $item=Announcement::create(['classroom_id'=>$classroom->id,'author_id'=>$request->user()->id,'title'=>$data['title'],'body'=>$data['body'],'is_pinned'=>$data['is_pinned']??false,'status'=>$publish?'published':'draft','published_at'=>$publish?now():null]);
        if($publish){$this->notifications->notifyClassroomStudents($classroom,'announcement','Pengumuman baru: '.$item->title,mb_strimwidth($item->body,0,180,'…'),'/communications',['announcement_id'=>$item->id,'classroom_id'=>$classroom->id]);}
        return response()->json(['data'=>$item->load('author:id,name,role')],201);
    }

    public function publishAnnouncement(Request $request, int $id): JsonResponse
    {
        $item=Announcement::with('classroom')->findOrFail($id);
        $classroom=$this->owned($request,$item->classroom_id);
        $data=$request->validate(['published'=>['required','boolean']]);
        $wasPublished=$item->status==='published';
        $item->update(['status'=>$data['published']?'published':'draft','published_at'=>$data['published']?($item->published_at??now()):null]);
        if($data['published']&&!$wasPublished){$this->notifications->notifyClassroomStudents($classroom,'announcement','Pengumuman baru: '.$item->title,mb_strimwidth($item->body,0,180,'…'),'/communications',['announcement_id'=>$item->id,'classroom_id'=>$classroom->id]);}
        return response()->json(['data'=>$item]);
    }

    public function destroyAnnouncement(Request $request, int $id): JsonResponse
    {
        $item=Announcement::findOrFail($id); $this->owned($request,$item->classroom_id); $item->delete(); return response()->json(null,204);
    }

    public function discussion(Request $request, int $classroomId): JsonResponse
    {
        $this->owned($request,$classroomId);
        $items=DiscussionPost::where('classroom_id',$classroomId)->whereNull('parent_id')->with(['user:id,name,role','replies.user:id,name,role'])->oldest()->get();
        return response()->json(['data'=>$items]);
    }

    public function postDiscussion(Request $request, int $classroomId): JsonResponse
    {
        $classroom=$this->owned($request,$classroomId);
        $data=$request->validate(['body'=>['required','string','max:5000'],'parent_id'=>['nullable','integer']]);
        if(!empty($data['parent_id'])) DiscussionPost::where('classroom_id',$classroom->id)->findOrFail($data['parent_id']);
        $item=DiscussionPost::create(['classroom_id'=>$classroom->id,'user_id'=>$request->user()->id,'parent_id'=>$data['parent_id']??null,'body'=>$data['body']]);
        if(empty($data['parent_id'])) $this->notifications->notifyClassroomStudents($classroom,'discussion','Diskusi baru di '.$classroom->title,mb_strimwidth($item->body,0,160,'…'),'/communications',['classroom_id'=>$classroom->id]);
        return response()->json(['data'=>$item->load('user:id,name,role')],201);
    }
}
