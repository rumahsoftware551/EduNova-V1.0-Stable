<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ClassGroup extends Model
{
    use HasFactory;
    protected $fillable=['school_id','academic_year_id','major_id','homeroom_teacher_id','name','grade_level','capacity','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','grade_level'=>'integer','capacity'=>'integer']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function homeroomTeacher(): BelongsTo { return $this->belongsTo(User::class,'homeroom_teacher_id'); }
    public function enrollments(): HasMany { return $this->hasMany(ClassEnrollment::class); }
    public function teachingAssignments(): HasMany { return $this->hasMany(TeachingAssignment::class); }
}
