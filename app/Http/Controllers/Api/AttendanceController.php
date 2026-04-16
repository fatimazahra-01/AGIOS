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

    private function computeStatus(Carbon $time): string
    {
        // Read thresholds from config (minutes after session start, e.g. 08:00)
        $lateAfter   = config('attendance.late_after_minutes', 10);
        $absentAfter = config('attendance.absent_after_minutes', 30);
        $sessionStart = Carbon::today()->setTimeFromTimeString(
            config('attendance.session_start', '08:00:00')
        );

        $diff = $sessionStart->diffInMinutes($time, false);

        if ($diff <= $lateAfter) return 'present';
        if ($diff <= $absentAfter) return 'late';
        return 'absent';
    }
}
