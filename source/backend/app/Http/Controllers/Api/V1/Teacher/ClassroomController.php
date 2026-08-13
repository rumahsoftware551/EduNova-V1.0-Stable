<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\TeachingAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassroomController extends Controller
{
    private function owned(Request $request)
    {
        return Classroom::query()
            ->where('school_id', $request->user()->school_id)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $request->user()->id));
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->owned($request)
            ->with([
                'teachingAssignment.subject:id,code,name',
                'teachingAssignment.classGroup:id,name,grade_level',
                'teachingAssignment.semester:id,name',
            ])
            ->withCount(['sections', 'materials'])
            ->latest()
            ->get();

        return response()->json(['data' => $items]);
    }

    public function meta(Request $request): JsonResponse
    {
        $assignments = TeachingAssignment::query()
            ->where('teacher_id', $request->user()->id)
            ->whereHas('classGroup', fn ($q) => $q->where('school_id', $request->user()->school_id))
            ->with(['subject:id,code,name', 'classGroup:id,name,grade_level', 'semester:id,name'])
            ->with('classroom:id,teaching_assignment_id,title,status')
            ->get();

        return response()->json(['data' => $assignments]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'teaching_assignment_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $assignment = TeachingAssignment::query()
            ->where('teacher_id', $request->user()->id)
            ->whereHas('classGroup', fn ($q) => $q->where('school_id', $request->user()->school_id))
            ->findOrFail($data['teaching_assignment_id']);

        $existing = Classroom::where('teaching_assignment_id', $assignment->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Penugasan ini sudah memiliki kelas digital.', 'data' => $existing], 422);
        }

        $classroom = Classroom::create([
            'school_id' => $request->user()->school_id,
            'teaching_assignment_id' => $assignment->id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'code' => $assignment->subject?->code,
            'description' => $data['description'] ?? null,
            'status' => 'draft',
        ]);

        return response()->json(['data' => $classroom->load([
            'teachingAssignment.subject:id,code,name',
            'teachingAssignment.classGroup:id,name,grade_level',
            'teachingAssignment.semester:id,name',
        ])], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $classroom = $this->owned($request)
            ->with([
                'teachingAssignment.subject:id,code,name',
                'teachingAssignment.classGroup:id,name,grade_level',
                'teachingAssignment.semester:id,name',
                'sections.materials',
            ])
            ->withCount(['sections', 'materials'])
            ->findOrFail($id);

        return response()->json(['data' => $classroom]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $classroom = $this->owned($request)->findOrFail($id);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $classroom->update($data);
        return response()->json(['data' => $classroom]);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $classroom = $this->owned($request)->with('sections.materials')->findOrFail($id);
        $data = $request->validate(['published' => ['required', 'boolean']]);

        if ($data['published']) {
            $publishedMaterials = $classroom->sections
                ->where('is_published', true)
                ->sum(fn ($section) => $section->materials->where('is_published', true)->count());
            if ($publishedMaterials < 1) {
                return response()->json(['message' => 'Publikasikan minimal satu materi sebelum kelas dibuka untuk siswa.'], 422);
            }
        }

        $classroom->update([
            'status' => $data['published'] ? 'published' : 'draft',
            'published_at' => $data['published'] ? now() : null,
        ]);

        return response()->json(['data' => $classroom->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $classroom = $this->owned($request)->with('sections.materials')->findOrFail($id);
        foreach ($classroom->sections as $section) {
            foreach ($section->materials as $material) {
                if ($material->file_path) Storage::disk('public')->delete($material->file_path);
            }
        }
        $classroom->delete();
        return response()->json(null, 204);
    }
}
