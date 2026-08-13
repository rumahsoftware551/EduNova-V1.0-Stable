<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TeacherProfile extends Model
{
    use HasFactory;
    protected $fillable=['user_id','employee_no','phone','expertise','is_homeroom_eligible'];
    protected function casts(): array { return ['is_homeroom_eligible'=>'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
