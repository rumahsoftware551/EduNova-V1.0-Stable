<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\HasMany;
class QuestionBankItem extends Model { use HasFactory; protected $fillable=['school_id','subject_id','created_by','type','question','explanation','default_points','is_active']; protected function casts():array{return['is_active'=>'boolean','default_points'=>'decimal:2'];} public function subject():BelongsTo{return $this->belongsTo(Subject::class);} public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} public function options():HasMany{return $this->hasMany(QuestionBankOption::class)->orderBy('position');} }
