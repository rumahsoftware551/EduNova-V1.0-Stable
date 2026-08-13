<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LearningMaterialController extends Controller
{
    private function section(Request $request, int $id): CourseSection
    {
        return CourseSection::query()
            ->whereHas('classroom', fn ($q) => $q
                ->where('school_id', $request->user()->school_id)
                ->whereHas('teachingAssignment', fn ($a) => $a->where('teacher_id', $request->user()->id)))
            ->findOrFail($id);
    }

    private function material(Request $request, int $id): LearningMaterial
    {
        return LearningMaterial::query()
            ->whereHas('section.classroom', fn ($q) => $q
                ->where('school_id', $request->user()->school_id)
                ->whereHas('teachingAssignment', fn ($a) => $a->where('teacher_id', $request->user()->id)))
            ->findOrFail($id);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(['video', 'pdf', 'link', 'text', 'file'])],
            'summary' => ['nullable', 'string', 'max:3000'],
            'content' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url', 'max:3000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:65536'],
        ];
    }

    public function store(Request $request, int $sectionId): JsonResponse
    {
        $section = $this->section($request, $sectionId);
        $data = $request->validate($this->rules());
        $file = $request->file('file');
        $position = (int) $section->materials()->max('position') + 1;

        if (in_array($data['type'], ['pdf', 'file'], true) && ! $file && empty($data['external_url'])) {
            return response()->json(['message' => 'Materi PDF/file membutuhkan unggahan file atau URL eksternal.'], 422);
        }

        $attributes = [
            'title' => $data['title'],
            'type' => $data['type'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'position' => max(1, $position),
            'is_preview' => $data['is_preview'] ?? false,
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
        ];

        if ($file) {
            $attributes['file_path'] = $file->store('materials', 'public');
            $attributes['original_name'] = $file->getClientOriginalName();
            $attributes['mime_type'] = $file->getMimeType();
            $attributes['file_size'] = $file->getSize();
        }

        $material = $section->materials()->create($attributes);
        return response()->json(['data' => $material], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $material = $this->material($request, $id);
        $data = $request->validate($this->rules());
        $file = $request->file('file');

        $attributes = [
            'title' => $data['title'],
            'type' => $data['type'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'is_preview' => $data['is_preview'] ?? false,
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) ? ($material->published_at ?? now()) : null,
        ];

        if ($file) {
            if ($material->file_path) Storage::disk('public')->delete($material->file_path);
            $attributes['file_path'] = $file->store('materials', 'public');
            $attributes['original_name'] = $file->getClientOriginalName();
            $attributes['mime_type'] = $file->getMimeType();
            $attributes['file_size'] = $file->getSize();
        }

        $material->update($attributes);
        return response()->json(['data' => $material->fresh()]);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $material = $this->material($request, $id);
        $data = $request->validate(['published' => ['required', 'boolean']]);
        $material->update([
            'is_published' => $data['published'],
            'published_at' => $data['published'] ? ($material->published_at ?? now()) : null,
        ]);
        return response()->json(['data' => $material->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $material = $this->material($request, $id);
        if ($material->file_path) Storage::disk('public')->delete($material->file_path);
        $material->delete();
        return response()->json(null, 204);
    }
}
