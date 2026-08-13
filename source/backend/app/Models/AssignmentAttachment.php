<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssignmentAttachment extends Model
{
    use HasFactory;
    protected $fillable=['assignment_id','file_path','original_name','mime_type','file_size'];
    protected $appends=['file_url'];
    protected function casts(): array { return ['file_size'=>'integer']; }
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function getFileUrlAttribute(): ?string { return $this->file_path ? url('/api/v1/assignment-files/attachment/'.$this->id) : null; }
}
