<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\LearningMaterial;
use App\Models\MaterialProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    private function accessible(Request $request, int $id): LearningMaterial
    {
        $classGroupIds = ClassEnrollment::query()
            ->where('student_id', $request->user()->id)
            ->where('status', 'active')
            ->pluck('class_group_id');

        return LearningMaterial::query()
            ->where('is_published', true)
            ->whereHas('section', fn ($q) => $q->where('is_published', true))
            ->whereHas('section.classroom', fn ($q) => $q
                ->where('school_id', $request->user()->school_id)
                ->where('status', 'published')
                ->whereHas('teachingAssignment', fn ($a) => $a->whereIn('class_group_id', $classGroupIds)))
            ->findOrFail($id);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $material = $this->accessible($request, $id)->load([
            'section.classroom.teachingAssignment.subject:id,code,name',
            'section.classroom.teachingAssignment.teacher:id,name',
        ]);

        $progress = MaterialProgress::where('learning_material_id', $material->id)
            ->where('student_id', $request->user()->id)
            ->first();

        $all = LearningMaterial::query()
            ->where('is_published', true)
            ->whereHas('section', fn ($q) => $q
                ->where('classroom_id', $material->section->classroom_id)
                ->where('is_published', true))
            ->with('section:id,classroom_id,position')
            ->get()
            ->sortBy(fn ($m) => sprintf('%05d-%05d', $m->section->position, $m->position))
            ->values();

        $index = $all->search(fn ($m) => $m->id === $material->id);
        $previous = $index !== false && $index > 0 ? $all[$index - 1] : null;
        $next = $index !== false && $index < $all->count() - 1 ? $all[$index + 1] : null;

        return response()->json(['data' => [
            'material' => $material,
            'progress' => $progress,
            'navigation' => [
                'previous' => $previous ? ['id' => $previous->id, 'title' => $previous->title] : null,
                'next' => $next ? ['id' => $next->id, 'title' => $next->title] : null,
            ],
        ]]);
    }

    public function progress(Request $request, int $id): JsonResponse
    {
        $material = $this->accessible($request, $id);
        $data = $request->validate([
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'last_position_seconds' => ['nullable', 'integer', 'min:0'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $record = MaterialProgress::firstOrNew([
            'learning_material_id' => $material->id,
            'student_id' => $request->user()->id,
        ]);

        $requestedComplete = (bool) ($data['completed'] ?? false);
        $percent = $requestedComplete ? 100 : ($data['progress_percent'] ?? max(1, (int) $record->progress_percent));
        $completed = $requestedComplete || $percent >= 100;

        if (! $record->exists) $record->first_opened_at = now();
        $record->progress_percent = $completed ? 100 : $percent;
        $record->last_position_seconds = $data['last_position_seconds'] ?? $record->last_position_seconds ?? 0;
        $record->last_opened_at = now();
        $record->status = $completed ? 'completed' : 'in_progress';
        $record->completed_at = $completed ? ($record->completed_at ?? now()) : null;
        $record->save();

        return response()->json(['data' => $record]);
    }
}
