<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $appends = [];
    protected $hidden = ['password', 'remember_token'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['role' => $this->role];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isProfessor(): bool
    {
        return $this->role === 'professor';
    }
    public function getAbsencesAttribute()
    {
        return $this->attendances()->where('status', 'absent')->count();
    }
    public function getStatusAttribute()
    {
        $abs = $this->absences;
        if ($abs >= 5) return 'Critique';
        if ($abs >= 3)  return 'Avertissement';
        return 'Actif';
    }
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
