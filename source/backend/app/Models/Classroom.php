<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['school_id','teaching_assignment_id','created_by','title','code','description','status','published_at'];

    protected function casts(): array { return ['published_at'=>'datetime']; }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function teachingAssignment(): BelongsTo { return $this->belongsTo(TeachingAssignment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function sections(): HasMany { return $this->hasMany(CourseSection::class)->orderBy('position'); }
    public function assignments(): HasMany { return $this->hasMany(Assignment::class)->orderByDesc('due_at'); }
    public function quizzes(): HasMany { return $this->hasMany(Quiz::class)->orderByDesc('created_at'); }
    public function materials(): HasManyThrough { return $this->hasManyThrough(LearningMaterial::class,CourseSection::class,'classroom_id','course_section_id'); }
    public function gradebookSetting(): HasOne { return $this->hasOne(GradebookSetting::class); }
    public function gradeAdjustments(): HasMany { return $this->hasMany(GradeAdjustment::class); }
    public function finalGradeRecords(): HasMany { return $this->hasMany(FinalGradeRecord::class); }
}
