<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AssignmentSubmission extends Model
{
    use HasFactory;
    protected $fillable=['assignment_id','student_id','status','text_answer','link_url','submitted_at','is_late','attempt_no','score','feedback','graded_by','graded_at','returned_at'];
    protected function casts(): array { return ['submitted_at'=>'datetime','is_late'=>'boolean','attempt_no'=>'integer','score'=>'decimal:2','graded_at'=>'datetime','returned_at'=>'datetime']; }
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function grader(): BelongsTo { return $this->belongsTo(User::class,'graded_by'); }
    public function files(): HasMany { return $this->hasMany(SubmissionFile::class,'submission_id'); }
    public function rubricScores(): HasMany { return $this->hasMany(RubricScore::class,'submission_id'); }
}
