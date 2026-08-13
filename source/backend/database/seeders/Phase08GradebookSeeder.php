<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\GradebookSetting;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class Phase08GradebookSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('username','susanto')->first();
        $student = User::where('username','andi.saputra')->first();
        $classroom = $teacher ? Classroom::where('created_by',$teacher->id)->first() : null;
        if (!$teacher || !$student || !$classroom) return;

        GradebookSetting::updateOrCreate(
            ['classroom_id'=>$classroom->id],
            ['assignment_weight'=>60,'quiz_weight'=>40,'passing_grade'=>75,'missing_as_zero'=>false,'status'=>'draft']
        );

        $quiz = Quiz::where('classroom_id',$classroom->id)->where('status','published')->with('questions.bankItem.options')->first();
        if (!$quiz || $quiz->attempts()->where('student_id',$student->id)->where('status','graded')->exists()) return;

        $attempt = QuizAttempt::updateOrCreate(
            ['quiz_id'=>$quiz->id,'student_id'=>$student->id,'attempt_no'=>1],
            [
                'status'=>'graded',
                'started_at'=>now()->subDays(2)->subMinutes(18),
                'expires_at'=>now()->subDays(2)->addMinutes(2),
                'submitted_at'=>now()->subDays(2),
                'auto_submitted'=>false,
                'objective_score'=>30,
                'essay_score'=>13,
                'total_score'=>43,
                'graded_by'=>$teacher->id,
                'graded_at'=>now()->subDay(),
            ]
        );

        foreach ($quiz->questions as $question) {
            $item = $question->bankItem;
            if ($item->type === 'essay') {
                QuizAnswer::updateOrCreate(
                    ['quiz_attempt_id'=>$attempt->id,'quiz_question_id'=>$question->id],
                    [
                        'text_answer'=>'Aperture, shutter speed, dan ISO saling memengaruhi exposure. Perubahan satu komponen perlu dikompensasikan oleh komponen lain sesuai kebutuhan visual.',
                        'awarded_points'=>13,
                        'teacher_feedback'=>'Konsep utama sudah benar. Tambahkan contoh kompensasi setting agar analisis lebih kuat.',
                        'saved_at'=>now()->subDays(2),
                    ]
                );
                continue;
            }

            $correct = $item->options->firstWhere('is_correct', true);
            QuizAnswer::updateOrCreate(
                ['quiz_attempt_id'=>$attempt->id,'quiz_question_id'=>$question->id],
                [
                    'selected_option_id'=>$item->type === 'multiple_choice' ? $correct?->id : null,
                    'boolean_answer'=>$item->type === 'true_false' ? ($correct?->option_text === 'Benar') : null,
                    'is_correct'=>true,
                    'awarded_points'=>(float)$question->points,
                    'saved_at'=>now()->subDays(2),
                ]
            );
        }
    }
}
