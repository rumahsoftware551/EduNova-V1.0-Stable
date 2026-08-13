<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\MaterialProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    private function enrolledClassGroupIds(Request $request)
    {
        return ClassEnrollment::query()
            ->where('student_id', $request->user()->id)
            ->where('status', 'active')
            ->pluck('class_group_id');
    }

    private function accessible(Request $request)
    {
        $classGroupIds = $this->enrolledClassGroupIds($request);

        return Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->where('status', 'published')
            ->whereHas('teachingAssignment', fn ($q) => $q->whereIn('class_group_id', $classGroupIds));
    }

    public function index(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $items = $this->accessible($request)
            ->with([
                'teachingAssignment.subject:id,code,name',
                'teachingAssignment.classGroup:id,name,grade_level',
                'teachingAssignment.teacher:id,name',
                'teachingAssignment.semester:id,name',
            ])
            ->withCount([
                'sections as sections_count' => fn ($q) => $q->where('is_published', true),
                'materials as materials_count' => fn ($q) => $q->where('learning_materials.is_published', true),
            ])
            ->latest('published_at')
            ->get();

        $items->each(function ($classroom) use ($studentId) {
            $materialIds = $classroom->materials()->where('learning_materials.is_published', true)->pluck('learning_materials.id');
            $completed = MaterialProgress::where('student_id', $studentId)
                ->whereIn('learning_material_id', $materialIds)
                ->where('status', 'completed')
                ->count();
            $total = max(1, $materialIds->count());
            $classroom->setAttribute('completed_materials_count', $completed);
            $classroom->setAttribute('progress_percent', (int) round(($completed / $total) * 100));
        });

        return response()->json(['data' => $items]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentId = $request->user()->id;
        $classroom = $this->accessible($request)
            ->with([
                'teachingAssignment.subject:id,code,name',
                'teachingAssignment.classGroup:id,name,grade_level',
                'teachingAssignment.teacher:id,name',
                'teachingAssignment.semester:id,name',
                'sections' => fn ($q) => $q->where('is_published', true)->orderBy('position'),
                'sections.materials' => fn ($q) => $q->where('is_published', true)->orderBy('position'),
            ])
            ->findOrFail($id);

        $materialIds = $classroom->sections->flatMap(fn ($section) => $section->materials)->pluck('id');
        $progress = MaterialProgress::where('student_id', $studentId)
            ->whereIn('learning_material_id', $materialIds)
            ->get()
            ->keyBy('learning_material_id');

        $completed = 0;
        foreach ($classroom->sections as $section) {
            foreach ($section->materials as $material) {
                $record = $progress->get($material->id);
                if ($record?->status === 'completed') $completed++;
                $material->setAttribute('student_progress', $record ? [
                    'status' => $record->status,
                    'progress_percent' => $record->progress_percent,
                    'last_position_seconds' => $record->last_position_seconds,
                    'completed_at' => $record->completed_at,
                ] : null);
            }
        }

        $total = max(1, $materialIds->count());
        $classroom->setAttribute('completed_materials_count', $completed);
        $classroom->setAttribute('materials_count', $materialIds->count());
        $classroom->setAttribute('progress_percent', (int) round(($completed / $total) * 100));

        return response()->json(['data' => $classroom]);
    }
}
