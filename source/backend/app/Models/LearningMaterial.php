<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id', 'title', 'type', 'summary', 'content', 'external_url',
        'file_path', 'original_name', 'mime_type', 'file_size', 'duration_minutes',
        'position', 'is_preview', 'is_published', 'published_at',
    ];

    protected $appends = ['file_url'];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'position' => 'integer',
            'duration_minutes' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public function section(): BelongsTo { return $this->belongsTo(CourseSection::class, 'course_section_id'); }
    public function progressRecords(): HasMany { return $this->hasMany(MaterialProgress::class); }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? url('/api/v1/material-files/'.$this->id) : null;
    }
}
