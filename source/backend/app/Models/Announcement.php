<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Announcement extends Model {
    use HasFactory;
    protected $fillable=['classroom_id','author_id','title','body','status','is_pinned','published_at'];
    protected function casts(): array { return ['is_pinned'=>'boolean','published_at'=>'datetime']; }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }
}
