<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'message',
        'file_path',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
