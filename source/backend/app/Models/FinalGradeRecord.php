<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalGradeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id', 'student_id', 'assignment_average', 'quiz_average',
        'adjustment', 'final_score', 'letter_grade', 'result_status',
        'published_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'assignment_average' => 'decimal:2',
            'quiz_average' => 'decimal:2',
            'adjustment' => 'decimal:2',
            'final_score' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function publisher(): BelongsTo { return $this->belongsTo(User::class, 'published_by'); }
}
