<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class RubricCriterion extends Model
{
    use HasFactory;
    protected $fillable=['assignment_id','title','description','max_points','position'];
    protected function casts(): array { return ['max_points'=>'decimal:2','position'=>'integer']; }
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function scores(): HasMany { return $this->hasMany(RubricScore::class); }
}
