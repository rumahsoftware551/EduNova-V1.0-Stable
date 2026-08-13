<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\FinalGradeRecord;
use App\Models\GradebookSetting;
use App\Services\GradebookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningAnalyticsController extends Controller
{
    public function __construct(private GradebookService $gradebook) {}

    public function __invoke(Request $request): JsonResponse
    {
        $classrooms = Classroom::query()
            ->where('school_id',$request->user()->school_id)
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name','teachingAssignment.teacher:id,name','gradebookSetting'])
            ->orderBy('title')
            ->get();

        $publishedIds = GradebookSetting::whereIn('classroom_id',$classrooms->pluck('id'))
            ->where('status','published')->pluck('classroom_id');

        $records = FinalGradeRecord::whereIn('classroom_id',$publishedIds)->get();
        $avg = $records->count() ? round((float)$records->avg('final_score'),2) : null;
        $passRate = $records->count() ? round(($records->where('result_status','passed')->count()/$records->count())*100,2) : null;

        $classes = $classrooms->map(function (Classroom $classroom) {
            $payload = $this->gradebook->classroomPayload($classroom);
            return [
                'id'=>$classroom->id,
                'title'=>$classroom->title,
                'subject'=>$classroom->teachingAssignment?->subject,
                'class_group'=>$classroom->teachingAssignment?->classGroup,
                'teacher'=>$classroom->teachingAssignment?->teacher,
                'grade_status'=>$payload['settings']['status'],
                'summary'=>$payload['summary'],
            ];
        })->values();

        return response()->json(['data'=>[
            'summary'=>[
                'classrooms'=>$classrooms->count(),
                'published_gradebooks'=>$publishedIds->count(),
                'published_students'=>$records->count(),
                'school_average'=>$avg,
                'pass_rate'=>$passRate,
                'at_risk'=>$classes->sum(fn($c)=>(int)$c['summary']['at_risk']),
            ],
            'classes'=>$classes,
        ]]);
    }
}
