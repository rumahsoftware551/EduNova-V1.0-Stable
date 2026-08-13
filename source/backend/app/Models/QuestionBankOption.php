<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class QuestionBankOption extends Model { use HasFactory; protected $fillable=['question_bank_item_id','option_text','is_correct','position']; protected function casts():array{return['is_correct'=>'boolean'];} public function item():BelongsTo{return $this->belongsTo(QuestionBankItem::class,'question_bank_item_id');} }
