<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeAdjustment extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_id', 'student_id', 'points', 'note', 'updated_by'];

    protected function casts(): array
    {
        return ['points' => 'decimal:2'];
    }

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
}
