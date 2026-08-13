<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentProfile extends Model
{
    use HasFactory;
    protected $fillable=['user_id','nis','nisn','gender','birth_place','birth_date','phone','parent_name','parent_phone'];
    protected function casts(): array { return ['birth_date'=>'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
