<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\CourseSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseSectionController extends Controller
{
    private function classroom(Request $request, int $id): Classroom
    {
        return Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $request->user()->id))
            ->findOrFail($id);
    }

    private function section(Request $request, int $id): CourseSection
    {
        return CourseSection::query()
            ->whereHas('classroom', fn ($q) => $q
                ->where('school_id', $request->user()->school_id)
                ->whereHas('teachingAssignment', fn ($a) => $a->where('teacher_id', $request->user()->id)))
            ->findOrFail($id);
    }

    public function store(Request $request, int $classroomId): JsonResponse
    {
        $classroom = $this->classroom($request, $classroomId);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $position = (int) $classroom->sections()->max('position') + 1;
        $section = $classroom->sections()->create([
            ...$data,
            'position' => max(1, $position),
            'is_published' => $data['is_published'] ?? true,
        ]);

        return response()->json(['data' => $section], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $section = $this->section($request, $id);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $section->update($data);
        return response()->json(['data' => $section]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $section = $this->section($request, $id)->load('materials');
        foreach ($section->materials as $material) {
            if ($material->file_path) Storage::disk('public')->delete($material->file_path);
        }
        $section->delete();
        return response()->json(null, 204);
    }
}
