<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubmissionFile extends Model
{
    use HasFactory;
    protected $fillable=['submission_id','file_path','original_name','mime_type','file_size'];
    protected $appends=['file_url'];
    protected function casts(): array { return ['file_size'=>'integer']; }
    public function submission(): BelongsTo { return $this->belongsTo(AssignmentSubmission::class,'submission_id'); }
    public function getFileUrlAttribute(): ?string { return $this->file_path ? url('/api/v1/assignment-files/submission/'.$this->id) : null; }
}
