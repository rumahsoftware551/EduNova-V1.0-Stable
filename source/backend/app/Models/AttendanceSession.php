<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AttendanceSession extends Model {
    use HasFactory;
    protected $fillable=['school_id','classroom_id','created_by','title','opens_at','late_after','closes_at','status','allow_manual','require_gps','latitude','longitude','radius_meters','require_dynamic_qr','qr_rotation_seconds','qr_secret','require_selfie','closed_at'];
    protected $hidden=['qr_secret'];
    protected function casts(): array { return ['opens_at'=>'datetime','late_after'=>'datetime','closes_at'=>'datetime','closed_at'=>'datetime','allow_manual'=>'boolean','require_gps'=>'boolean','require_dynamic_qr'=>'boolean','require_selfie'=>'boolean','latitude'=>'float','longitude'=>'float']; }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function records(): HasMany { return $this->hasMany(AttendanceRecord::class); }
}
