<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RubricScore extends Model
{
    use HasFactory;
    protected $fillable=['submission_id','rubric_criterion_id','points','comment'];
    protected function casts(): array { return ['points'=>'decimal:2']; }
    public function submission(): BelongsTo { return $this->belongsTo(AssignmentSubmission::class,'submission_id'); }
    public function criterion(): BelongsTo { return $this->belongsTo(RubricCriterion::class,'rubric_criterion_id'); }
}
