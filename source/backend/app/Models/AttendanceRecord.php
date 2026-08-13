<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceRecord extends Model {
    use HasFactory;
    protected $fillable=['attendance_session_id','student_id','status','method','checked_in_at','latitude','longitude','distance_meters','gps_accuracy','selfie_path','verified_by','note','meta'];
    protected function casts(): array { return ['checked_in_at'=>'datetime','latitude'=>'float','longitude'=>'float','distance_meters'=>'float','gps_accuracy'=>'float','meta'=>'array']; }
    public function session(): BelongsTo { return $this->belongsTo(AttendanceSession::class,'attendance_session_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class,'verified_by'); }
}
