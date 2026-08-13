<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClassEnrollment extends Model
{
    use HasFactory;
    protected $fillable=['class_group_id','student_id','enrolled_at','status'];
    protected function casts(): array { return ['enrolled_at'=>'date']; }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
}
