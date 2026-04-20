<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Device scans card/fingerprint → marks attendance
    public function scan(Request $request)
    {
        $request->validate([
            'identifier_type'  => 'required|in:card_id,fingerprint_id',
            'identifier_value' => 'required',
        ]);

        $student = Student::where(
            $request->identifier_type,
            $request->identifier_value
        )->where('is_active', true)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found or inactive'], 404);
        }

        $now    = Carbon::now();
        $date   = $now->toDateString();
        $time   = $now->toTimeString();
        $status = $this->computeStatus($now);

        // Prevent duplicate scan for same date
        $existing = Attendance::where('student_id', $student->id)
            ->where('date', $date)->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already scanned today',
                'attendance' => $existing,
            ], 409);
        }

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'date'       => $date,
            'time'       => $time,
            'status'     => $status,
        ]);

        return response()->json([
            'message'    => 'Attendance recorded',
            'student'    => $student->name,
            'status'     => $status,
            'attendance' => $attendance,
        ], 201);
    }

    // Get all attendances (admin/professor)
    public function index(Request $request)
    {
        $query = Attendance::with('student');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('class')) {
            $query->whereHas('student', fn($q) => $q->where('class', $request->class));
        }

        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    // Student: view own attendance history
    public function studentHistory(Student $student)
    {
        return response()->json(
            $student->attendances()->orderByDesc('date')->get()
        );
    }

    // Realtime monitoring — today's attendance
    public function realtime()
    {
        $today = Carbon::today()->toDateString();
        $data  = Attendance::with('student')
            ->where('date', $today)
            ->get()
            ->map(fn($a) => [
                'student'  => $a->student->name,
                'class'    => $a->student->class,
                'time'     => $a->time,
                'status'   => $a->status,
            ]);

        return response()->json([
            'date'  => $today,
            'total' => $data->count(),
            'data'  => $data,
        ]);
    }

    public function summary()
    {
        $today = Carbon::today()->toDateString();
        
        $totalStudents = Student::where('is_active', true)->count();
        $presentToday  = Attendance::where('date', $today)->where('status', 'present')->count();
        $lateToday     = Attendance::where('date', $today)->where('status', 'late')->count();
        
        // Total présents (inclus les retards)
        $totalPresentToday = $presentToday + $lateToday;
        
        // Les absents sont ceux qui n'ont pas encore scanné aujourd'hui
        $absentToday   = $totalStudents - $totalPresentToday;
        $pendingJustif = \App\Models\Justification::where('status', 'pending')->count();

        return response()->json([
            'total_students' => $totalStudents,
            'present_today'  => $presentToday,
            'late_today'     => $lateToday,
            'total_present'  => $totalPresentToday,
            'absent_today'   => max(0, $absentToday),
            'pending_justifications' => $pendingJustif,
            'attendance_rate' => $totalStudents > 0 ? round(($totalPresentToday / $totalStudents) * 100, 1) : 0,
        ]);
    }

    private function computeStatus(Carbon $time): string
    {
        $lateAfter   = (int) \Illuminate\Support\Facades\Cache::get('config.late_after_minutes', 10);
        $absentAfter = (int) \Illuminate\Support\Facades\Cache::get('config.absent_after_minutes', 30);
        $sessionStartStr = \Illuminate\Support\Facades\Cache::get('config.session_start', '08:00:00');
        
        $sessionStart = Carbon::today()->setTimeFromTimeString($sessionStartStr);
        $diff = $sessionStart->diffInMinutes($time, false);

        // Si l'étudiant scanne AVANT l'heure ou dans les 5 premières minutes (grâce)
        if ($diff <= 5) return 'present';
        // Si l'étudiant scanne après la grâce mais avant le seuil d'absence
        if ($diff <= $absentAfter) return 'late';
        // Au delà du seuil d'absence
        return 'absent';
    }
}
