<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class TeachingAssignment extends Model
{
    use HasFactory;
    protected $fillable=['class_group_id','subject_id','teacher_id','semester_id','weekly_hours'];
    protected function casts(): array { return ['weekly_hours'=>'integer']; }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function classroom(): HasOne { return $this->hasOne(Classroom::class); }
}
