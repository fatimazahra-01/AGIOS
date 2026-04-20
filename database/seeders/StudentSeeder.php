<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'Youssef Alami',    'class' => '3IIR-A', 'card_id' => 'CARD001', 'fingerprint_id' => 1],
            ['name' => 'Salma Bennani',    'class' => '3IIR-A', 'card_id' => 'CARD002', 'fingerprint_id' => 2],
            ['name' => 'Amine Chakir',     'class' => '3IIR-B', 'card_id' => 'CARD003', 'fingerprint_id' => 3],
            ['name' => 'Nadia El Fassi',   'class' => '3IIR-B', 'card_id' => 'CARD004', 'fingerprint_id' => 4],
            ['name' => 'Omar Tahiri',      'class' => '3IIR-A', 'card_id' => 'CARD005', 'fingerprint_id' => 5],
        ];

        foreach ($students as $studentData) {
            $s = Student::create(array_merge($studentData, ['is_active' => true]));
            
            // Link to user if name matches
            $user = \App\Models\User::where('name', $s->name)->first();
            if ($user) {
                $s->user_id = $user->id;
                $s->save();
            }
        }
    }
}
