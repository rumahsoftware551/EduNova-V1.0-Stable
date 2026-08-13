<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class School extends Model
{
    use HasFactory;
    protected $fillable=['name','code','npsn','slug','timezone','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function academicYears(): HasMany { return $this->hasMany(AcademicYear::class); }
    public function majors(): HasMany { return $this->hasMany(Major::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
    public function classGroups(): HasMany { return $this->hasMany(ClassGroup::class); }
}
