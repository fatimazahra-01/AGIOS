<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class',
        'card_id',
        'fingerprint_id',
        'is_active'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }
}
