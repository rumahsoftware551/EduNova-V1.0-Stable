<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialFileController extends Controller
{
    public function __invoke(Request $request, int $id): StreamedResponse
    {
        $user = $request->user();
        $material = LearningMaterial::query()->with('section.classroom.teachingAssignment')->findOrFail($id);
        $assignment = $material->section->classroom->teachingAssignment;

        $allowed = false;
        if ($user->role === 'teacher') {
            $allowed = $assignment->teacher_id === $user->id && $material->section->classroom->school_id === $user->school_id;
        } elseif ($user->role === 'student') {
            $allowed = $material->is_published
                && $material->section->is_published
                && $material->section->classroom->status === 'published'
                && ClassEnrollment::where('student_id', $user->id)
                    ->where('class_group_id', $assignment->class_group_id)
                    ->where('status', 'active')
                    ->exists();
        } elseif ($user->role === 'admin') {
            $allowed = $material->section->classroom->school_id === $user->school_id;
        }

        abort_unless($allowed, 403);
        abort_unless($material->file_path && Storage::disk('public')->exists($material->file_path), 404);

        return Storage::disk('public')->response(
            $material->file_path,
            $material->original_name,
            ['Content-Type' => $material->mime_type ?: 'application/octet-stream']
        );
    }
}
