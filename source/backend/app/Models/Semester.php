<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Semester extends Model
{
    use HasFactory;
    protected $fillable=['school_id','academic_year_id','name','number','starts_on','ends_on','is_active'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date','is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
}
