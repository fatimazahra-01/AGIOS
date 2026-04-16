<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $statuses = ['present', 'absent', 'late'];

        foreach ($students as $student) {
            for ($i = 0; $i < 10; $i++) {
                Attendance::create([
                    'student_id' => $student->id,
                    'date'       => now()->subDays($i)->toDateString(),
                    'time'       => now()->toTimeString(),
                    'status'     => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
