<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\FinalGradeRecord;
use App\Models\GradeAdjustment;
use App\Models\GradebookSetting;
use App\Services\GradebookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    public function __construct(private GradebookService $gradebook) {}

    private function owned(Request $request, int $id): Classroom
    {
        return Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $request->user()->id))
            ->findOrFail($id);
    }

    public function index(Request $request): JsonResponse
    {
        $items = Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $request->user()->id))
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name','gradebookSetting'])
            ->orderBy('title')
            ->get()
            ->map(function (Classroom $classroom) {
                $payload = $this->gradebook->classroomPayload($classroom);
                return [
                    'id' => $classroom->id,
                    'title' => $classroom->title,
                    'subject' => $classroom->teachingAssignment?->subject,
                    'class_group' => $classroom->teachingAssignment?->classGroup,
                    'settings' => $payload['settings'],
                    'summary' => $payload['summary'],
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function show(Request $request, int $classroomId): JsonResponse
    {
        return response()->json(['data' => $this->gradebook->classroomPayload($this->owned($request, $classroomId))]);
    }

    public function updateSettings(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->owned($request, $classroomId);
        $data = $request->validate([
            'assignment_weight' => ['required','numeric','min:0','max:100'],
            'quiz_weight' => ['required','numeric','min:0','max:100'],
            'passing_grade' => ['required','numeric','min:0','max:100'],
            'missing_as_zero' => ['required','boolean'],
        ]);

        if (abs(((float)$data['assignment_weight'] + (float)$data['quiz_weight']) - 100) > 0.01) {
            return response()->json(['message' => 'Total bobot Tugas + Quiz harus 100%.'], 422);
        }

        $setting = GradebookSetting::updateOrCreate(
            ['classroom_id' => $classroom->id],
            [...$data, 'status'=>'draft', 'published_by'=>null, 'published_at'=>null]
        );

        return response()->json(['data' => $setting]);
    }

    public function adjust(Request $request, int $classroomId, int $studentId): JsonResponse
    {
        $classroom = $this->owned($request, $classroomId);
        $classroom->loadMissing('teachingAssignment');
        $validStudent = \App\Models\ClassEnrollment::query()
            ->where('class_group_id', $classroom->teachingAssignment?->class_group_id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();

        if (!$validStudent) return response()->json(['message'=>'Siswa tidak terdaftar di kelas ini.'], 422);

        $data = $request->validate([
            'points' => ['required','numeric','min:-20','max:20'],
            'note' => ['nullable','string','max:2000'],
        ]);

        $item = GradeAdjustment::updateOrCreate(
            ['classroom_id'=>$classroom->id,'student_id'=>$studentId],
            ['points'=>$data['points'],'note'=>$data['note']??null,'updated_by'=>$request->user()->id]
        );

        GradebookSetting::where('classroom_id',$classroom->id)->update([
            'status'=>'draft','published_by'=>null,'published_at'=>null,
        ]);

        return response()->json(['data'=>$item]);
    }

    public function publish(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->owned($request, $classroomId);

        return DB::transaction(function () use ($request, $classroom) {
            $payload = $this->gradebook->classroomPayload($classroom);
            $now = now();

            FinalGradeRecord::where('classroom_id', $classroom->id)->delete();

            foreach ($payload['students'] as $row) {
                if ($row['final_score'] === null) continue;
                FinalGradeRecord::updateOrCreate(
                    ['classroom_id'=>$classroom->id,'student_id'=>$row['student']['id']],
                    [
                        'assignment_average'=>$row['assignment_average'],
                        'quiz_average'=>$row['quiz_average'],
                        'adjustment'=>$row['adjustment'],
                        'final_score'=>$row['final_score'],
                        'letter_grade'=>$row['letter_grade'],
                        'result_status'=>$row['passed']?'passed':'not_passed',
                        'published_by'=>$request->user()->id,
                        'published_at'=>$now,
                    ]
                );
            }

            GradebookSetting::updateOrCreate(
                ['classroom_id'=>$classroom->id],
                ['assignment_weight'=>60,'quiz_weight'=>40,'passing_grade'=>75,'missing_as_zero'=>false]
            )->update(['status'=>'published','published_by'=>$request->user()->id,'published_at'=>$now]);

            return response()->json(['data'=>$this->gradebook->classroomPayload($classroom->fresh())]);
        });
    }

    public function unpublish(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->owned($request, $classroomId);
        GradebookSetting::where('classroom_id',$classroom->id)->update([
            'status'=>'draft','published_by'=>null,'published_at'=>null,
        ]);

        return response()->json(['data'=>$this->gradebook->classroomPayload($classroom)]);
    }
}
