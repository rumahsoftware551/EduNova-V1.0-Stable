<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\GradeAdjustment;
use App\Models\GradebookSetting;
use App\Models\LearningMaterial;
use App\Models\MaterialProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class GradebookService
{
    public function setting(Classroom $classroom): GradebookSetting
    {
        return GradebookSetting::firstOrCreate(
            ['classroom_id' => $classroom->id],
            ['assignment_weight' => 60, 'quiz_weight' => 40, 'passing_grade' => 75, 'missing_as_zero' => false, 'status' => 'draft']
        );
    }

    public function classroomPayload(Classroom $classroom, ?int $onlyStudentId = null): array
    {
        $classroom->loadMissing([
            'teachingAssignment.subject:id,code,name',
            'teachingAssignment.classGroup:id,name',
            'teachingAssignment.semester:id,name,number',
            'teachingAssignment.teacher:id,name',
        ]);

        $setting = $this->setting($classroom);
        $classGroupId = $classroom->teachingAssignment?->class_group_id;

        $studentIds = ClassEnrollment::query()
            ->where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->when($onlyStudentId, fn ($q) => $q->where('student_id', $onlyStudentId))
            ->pluck('student_id');

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id','name','username','subtitle']);

        $assignments = Assignment::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'published')
            ->orderBy('due_at')
            ->get(['id','title','max_score','due_at']);

        $quizzes = Quiz::query()
            ->select(['id','classroom_id','title','closes_at','created_at'])
            ->where('classroom_id', $classroom->id)
            ->where('status', 'published')
            ->withSum('questions as max_score', 'points')
            ->orderBy('created_at')
            ->get();

        $publishedMaterialIds = LearningMaterial::query()
            ->whereHas('section', fn ($q) => $q->where('classroom_id', $classroom->id))
            ->where('is_published', true)
            ->pluck('id');

        $adjustments = GradeAdjustment::query()
            ->where('classroom_id', $classroom->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(fn (User $student) => $this->studentRow(
            $student, $classroom, $setting, $assignments, $quizzes, $publishedMaterialIds, $adjustments->get($student->id)
        ))->values();

        $scored = $rows->whereNotNull('final_score');
        $avg = $scored->count() ? round((float) $scored->avg('final_score'), 2) : null;
        $pass = $scored->count() ? round(($scored->where('passed', true)->count() / $scored->count()) * 100, 2) : null;

        return [
            'classroom' => [
                'id' => $classroom->id,
                'title' => $classroom->title,
                'code' => $classroom->code,
                'subject' => $classroom->teachingAssignment?->subject,
                'class_group' => $classroom->teachingAssignment?->classGroup,
                'semester' => $classroom->teachingAssignment?->semester,
                'teacher' => $classroom->teachingAssignment?->teacher,
            ],
            'settings' => [
                'assignment_weight' => (float) $setting->assignment_weight,
                'quiz_weight' => (float) $setting->quiz_weight,
                'passing_grade' => (float) $setting->passing_grade,
                'missing_as_zero' => (bool) $setting->missing_as_zero,
                'status' => $setting->status,
                'published_at' => $setting->published_at,
            ],
            'assessments' => [
                'assignments' => $assignments->map(fn ($a) => [
                    'id' => $a->id, 'title' => $a->title, 'max_score' => (float) $a->max_score, 'due_at' => $a->due_at,
                ])->values(),
                'quizzes' => $quizzes->map(fn ($q) => [
                    'id' => $q->id, 'title' => $q->title, 'max_score' => (float) ($q->max_score ?? 0), 'closes_at' => $q->closes_at,
                ])->values(),
            ],
            'students' => $rows,
            'summary' => [
                'students' => $rows->count(),
                'scored_students' => $scored->count(),
                'class_average' => $avg,
                'pass_rate' => $pass,
                'at_risk' => $rows->filter(fn ($r) => count($r['risk_flags']) > 0)->count(),
                'published_materials' => $publishedMaterialIds->count(),
            ],
        ];
    }

    private function studentRow(
        User $student,
        Classroom $classroom,
        GradebookSetting $setting,
        Collection $assignments,
        Collection $quizzes,
        Collection $publishedMaterialIds,
        $adjustment
    ): array {
        $assignmentScores = [];
        $missingAssignments = 0;

        foreach ($assignments as $assignment) {
            $submission = AssignmentSubmission::query()
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['graded','returned'])
                ->whereNotNull('score')
                ->first();

            $isPastDue = $assignment->due_at && now()->gt($assignment->due_at);
            if ($submission) {
                $pct = (float) $assignment->max_score > 0
                    ? round(((float) $submission->score / (float) $assignment->max_score) * 100, 2)
                    : 0;
                $assignmentScores[] = $pct;
            } elseif ($isPastDue) {
                $missingAssignments++;
                if ($setting->missing_as_zero) $assignmentScores[] = 0;
            }
        }

        $quizScores = [];
        $missingQuizzes = 0;

        foreach ($quizzes as $quiz) {
            $maxScore = (float) ($quiz->max_score ?? 0);
            $best = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->where('status', 'graded')
                ->whereNotNull('total_score')
                ->orderByDesc('total_score')
                ->first();

            $isClosed = $quiz->closes_at && now()->gt($quiz->closes_at);
            if ($best && $maxScore > 0) {
                $quizScores[] = round(((float) $best->total_score / $maxScore) * 100, 2);
            } elseif ($isClosed) {
                $missingQuizzes++;
                if ($setting->missing_as_zero) $quizScores[] = 0;
            }
        }

        $assignmentAverage = count($assignmentScores) ? round(array_sum($assignmentScores) / count($assignmentScores), 2) : null;
        $quizAverage = count($quizScores) ? round(array_sum($quizScores) / count($quizScores), 2) : null;
        $adjustmentPoints = $adjustment ? (float) $adjustment->points : 0;

        $weighted = 0.0;
        $usedWeight = 0.0;

        if ($assignmentAverage !== null) {
            $weighted += $assignmentAverage * (float) $setting->assignment_weight;
            $usedWeight += (float) $setting->assignment_weight;
        }
        if ($quizAverage !== null) {
            $weighted += $quizAverage * (float) $setting->quiz_weight;
            $usedWeight += (float) $setting->quiz_weight;
        }

        $final = $usedWeight > 0 ? round(($weighted / $usedWeight) + $adjustmentPoints, 2) : null;
        if ($final !== null) $final = max(0, min(100, $final));

        $totalMaterials = $publishedMaterialIds->count();
        $completed = $totalMaterials
            ? MaterialProgress::query()->where('student_id', $student->id)->whereIn('learning_material_id', $publishedMaterialIds)->where('status', 'completed')->count()
            : 0;
        $materialProgress = $totalMaterials ? round(($completed / $totalMaterials) * 100, 2) : 0;

        $risk = [];
        if ($final !== null && $final < (float) $setting->passing_grade) $risk[] = 'nilai_rendah';
        if (($missingAssignments + $missingQuizzes) >= 1) $risk[] = 'penilaian_terlewat';
        if ($totalMaterials > 0 && $materialProgress < 50) $risk[] = 'progress_belajar_rendah';

        return [
            'student' => ['id'=>$student->id,'name'=>$student->name,'username'=>$student->username,'subtitle'=>$student->subtitle],
            'assignment_average' => $assignmentAverage,
            'quiz_average' => $quizAverage,
            'adjustment' => $adjustmentPoints,
            'adjustment_note' => $adjustment?->note,
            'final_score' => $final,
            'letter_grade' => $this->letter($final),
            'passed' => $final !== null ? $final >= (float) $setting->passing_grade : false,
            'material_progress' => $materialProgress,
            'missing_count' => $missingAssignments + $missingQuizzes,
            'risk_flags' => $risk,
        ];
    }

    public function letter(?float $score): ?string
    {
        if ($score === null) return null;
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}
