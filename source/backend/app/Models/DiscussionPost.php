<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class DiscussionPost extends Model {
    use HasFactory;
    protected $fillable=['classroom_id','user_id','parent_id','body'];
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class,'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(self::class,'parent_id'); }
}
