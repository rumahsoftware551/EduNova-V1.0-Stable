<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['school_id','name','username','email','email_verified_at','password','role','status','subtitle'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function studentProfile(): HasOne { return $this->hasOne(StudentProfile::class); }
    public function teacherProfile(): HasOne { return $this->hasOne(TeacherProfile::class); }
    public function getInitialsAttribute(): string { return Str::of($this->name)->explode(' ')->filter()->take(2)->map(fn(string $part)=>Str::upper(Str::substr($part,0,1)))->implode(''); }
}
