<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\FinalGradeRecord;
use App\Services\GradebookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(private GradebookService $gradebook) {}

    private function available(Request $request)
    {
        return Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->whereHas('teachingAssignment.classGroup.enrollments', fn ($q) =>
                $q->where('student_id', $request->user()->id)->where('status','active')
            );
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->available($request)
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name','gradebookSetting'])
            ->orderBy('title')
            ->get()
            ->map(function (Classroom $classroom) use ($request) {
                $record = FinalGradeRecord::where('classroom_id',$classroom->id)
                    ->where('student_id',$request->user()->id)->first();
                $published = $classroom->gradebookSetting?->status === 'published';

                return [
                    'id'=>$classroom->id,
                    'title'=>$classroom->title,
                    'subject'=>$classroom->teachingAssignment?->subject,
                    'class_group'=>$classroom->teachingAssignment?->classGroup,
                    'published'=>$published,
                    'published_at'=>$published ? $classroom->gradebookSetting?->published_at : null,
                    'final_grade'=>$published && $record ? [
                        'assignment_average'=>$record->assignment_average !== null ? (float)$record->assignment_average : null,
                        'quiz_average'=>$record->quiz_average !== null ? (float)$record->quiz_average : null,
                        'adjustment'=>(float)$record->adjustment,
                        'final_score'=>(float)$record->final_score,
                        'letter_grade'=>$record->letter_grade,
                        'result_status'=>$record->result_status,
                    ] : null,
                ];
            });

        return response()->json(['data'=>$items]);
    }

    public function show(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->available($request)->findOrFail($classroomId);
        $payload = $this->gradebook->classroomPayload($classroom, $request->user()->id);
        $row = $payload['students'][0] ?? null;
        $setting = $classroom->gradebookSetting()->first();
        $published = $setting?->status === 'published';
        $record = $published
            ? FinalGradeRecord::where('classroom_id',$classroom->id)->where('student_id',$request->user()->id)->first()
            : null;

        return response()->json(['data'=>[
            'classroom'=>$payload['classroom'],
            'settings'=>$payload['settings'],
            'progress'=>$row ? [
                'assignment_average'=>$row['assignment_average'],
                'quiz_average'=>$row['quiz_average'],
                'material_progress'=>$row['material_progress'],
                'missing_count'=>$row['missing_count'],
            ] : null,
            'published'=>$published,
            'final_grade'=>$record ? [
                'assignment_average'=>$record->assignment_average !== null ? (float)$record->assignment_average : null,
                'quiz_average'=>$record->quiz_average !== null ? (float)$record->quiz_average : null,
                'adjustment'=>(float)$record->adjustment,
                'final_score'=>(float)$record->final_score,
                'letter_grade'=>$record->letter_grade,
                'result_status'=>$record->result_status,
                'published_at'=>$record->published_at,
            ] : null,
        ]]);
    }
}
