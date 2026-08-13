<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class QuizQuestion extends Model { use HasFactory; protected $fillable=['quiz_id','question_bank_item_id','points','position']; protected function casts():array{return['points'=>'decimal:2'];} public function quiz():BelongsTo{return $this->belongsTo(Quiz::class);} public function bankItem():BelongsTo{return $this->belongsTo(QuestionBankItem::class,'question_bank_item_id');} }
