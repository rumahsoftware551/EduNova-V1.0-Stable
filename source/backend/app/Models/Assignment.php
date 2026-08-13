<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Assignment extends Model
{
    use HasFactory;
    protected $fillable=['classroom_id','created_by','title','instructions','opens_at','due_at','max_score','allow_text','allow_link','allow_file','allow_resubmit','max_file_mb','status','published_at'];
    protected function casts(): array { return ['opens_at'=>'datetime','due_at'=>'datetime','published_at'=>'datetime','max_score'=>'decimal:2','allow_text'=>'boolean','allow_link'=>'boolean','allow_file'=>'boolean','allow_resubmit'=>'boolean','max_file_mb'=>'integer']; }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function attachments(): HasMany { return $this->hasMany(AssignmentAttachment::class); }
    public function rubricCriteria(): HasMany { return $this->hasMany(RubricCriterion::class)->orderBy('position'); }
    public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
}
