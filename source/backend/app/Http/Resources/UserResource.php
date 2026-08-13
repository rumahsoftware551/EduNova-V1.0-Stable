<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'initials' => $this->initials,
            'school' => $this->school?->name ?? 'EduNova',
            'subtitle' => $this->subtitle ?? match ($this->role) {
                'student' => 'Siswa',
                'teacher' => 'Guru',
                'admin' => 'Administrator',
                default => 'Pengguna EduNova',
            },
        ];
    }
}
