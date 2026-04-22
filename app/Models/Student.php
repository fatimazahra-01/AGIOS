<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'class',
        'card_id',
        'fingerprint_id',
        'is_active'
    ];

    protected $appends = ['absences', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAbsencesAttribute()
    {
        return $this->attendances()->where('status', 'absent')->count();
    }

    public function getStatusAttribute()
    {
        $abs = $this->absences;
        $seuilWarn = (int) \Illuminate\Support\Facades\Cache::get('config.seuil_warn', 3);
        $seuilCrit = (int) \Illuminate\Support\Facades\Cache::get('config.seuil_crit', 5);
        
        if ($abs >= $seuilCrit) return 'Critique';
        if ($abs >= $seuilWarn) return 'Avertissement';
        return 'Actif';
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }
}
