<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceEvidenceController extends Controller
{
    public function __invoke(Request $request, int $recordId): StreamedResponse
    {
        $record = AttendanceRecord::with('session.classroom.teachingAssignment')->findOrFail($recordId);
        $user = $request->user();
        $allowed = $record->student_id === $user->id
            || ($user->role === 'teacher' && $record->session?->classroom?->teachingAssignment?->teacher_id === $user->id)
            || ($user->role === 'admin' && $record->session?->school_id === $user->school_id);
        abort_unless($allowed && $record->selfie_path, 404);
        abort_unless(Storage::disk('public')->exists($record->selfie_path), 404);
        return Storage::disk('public')->download($record->selfie_path);
    }
}
