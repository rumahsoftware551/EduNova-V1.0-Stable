<?php
namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\DiscussionPost;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    private function accessible(Request $request, int $classroomId): Classroom
    {
        $ids=ClassEnrollment::where('student_id',$request->user()->id)->where('status','active')->pluck('class_group_id');
        return Classroom::where('school_id',$request->user()->school_id)->where('status','published')
            ->whereHas('teachingAssignment',fn($q)=>$q->whereIn('class_group_id',$ids))
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])
            ->findOrFail($classroomId);
    }

    public function classrooms(Request $request): JsonResponse
    {
        $ids=ClassEnrollment::where('student_id',$request->user()->id)->where('status','active')->pluck('class_group_id');
        $items=Classroom::where('school_id',$request->user()->school_id)->where('status','published')->whereHas('teachingAssignment',fn($q)=>$q->whereIn('class_group_id',$ids))->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])->orderBy('title')->get();
        return response()->json(['data'=>$items]);
    }

    public function announcements(Request $request, int $classroomId): JsonResponse
    {
        $this->accessible($request,$classroomId);
        $items=Announcement::where('classroom_id',$classroomId)->where('status','published')->with('author:id,name,role')->orderByDesc('is_pinned')->latest('published_at')->get();
        return response()->json(['data'=>$items]);
    }

    public function discussion(Request $request, int $classroomId): JsonResponse
    {
        $this->accessible($request,$classroomId);
        $items=DiscussionPost::where('classroom_id',$classroomId)->whereNull('parent_id')->with(['user:id,name,role','replies.user:id,name,role'])->oldest()->get();
        return response()->json(['data'=>$items]);
    }

    public function postDiscussion(Request $request, int $classroomId): JsonResponse
    {
        $classroom=$this->accessible($request,$classroomId);
        $data=$request->validate(['body'=>['required','string','max:5000'],'parent_id'=>['nullable','integer']]);
        if(!empty($data['parent_id'])) DiscussionPost::where('classroom_id',$classroom->id)->findOrFail($data['parent_id']);
        $item=DiscussionPost::create(['classroom_id'=>$classroom->id,'user_id'=>$request->user()->id,'parent_id'=>$data['parent_id']??null,'body'=>$data['body']]);
        $teacherId=$classroom->teachingAssignment?->teacher_id;
        if($teacherId && $teacherId!==$request->user()->id) UserNotification::create(['user_id'=>$teacherId,'type'=>'discussion','title'=>'Aktivitas diskusi di '.$classroom->title,'body'=>mb_strimwidth($item->body,0,160,'…'),'action_url'=>'/communications','data'=>['classroom_id'=>$classroom->id]]);
        return response()->json(['data'=>$item->load('user:id,name,role')],201);
    }
}
