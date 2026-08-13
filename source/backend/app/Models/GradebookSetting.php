<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradebookSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id', 'assignment_weight', 'quiz_weight', 'passing_grade',
        'missing_as_zero', 'status', 'published_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'assignment_weight' => 'decimal:2',
            'quiz_weight' => 'decimal:2',
            'passing_grade' => 'decimal:2',
            'missing_as_zero' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function publisher(): BelongsTo { return $this->belongsTo(User::class, 'published_by'); }
}
