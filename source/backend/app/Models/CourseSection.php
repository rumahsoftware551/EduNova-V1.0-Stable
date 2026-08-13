<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_id', 'title', 'description', 'position', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'position' => 'integer'];
    }

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function materials(): HasMany { return $this->hasMany(LearningMaterial::class)->orderBy('position'); }
}
