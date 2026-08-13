<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialProgress extends Model
{
    use HasFactory;

    protected $table = 'material_progress';

    protected $fillable = [
        'learning_material_id', 'student_id', 'status', 'progress_percent',
        'last_position_seconds', 'first_opened_at', 'last_opened_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
            'last_position_seconds' => 'integer',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function material(): BelongsTo { return $this->belongsTo(LearningMaterial::class, 'learning_material_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
}
