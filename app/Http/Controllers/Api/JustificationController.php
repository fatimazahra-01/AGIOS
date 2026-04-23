<?php

namespace App\Http\Controllers\Api;

use App\Models\Justification;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JustificationController extends Controller
{
    // Student submits a justification for themselves
    public function storeMe(Request $request)
    {
        $user = auth('api')->user();
        if (!$user->student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }

        $request->validate([
            'message'  => 'required|string',
            'file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('justifications', 'public');
        }

        $justification = Justification::create([
            'student_id'    => $user->student->id,
            'attendance_id' => $request->absence_id,
            'type'          => 'justification',
            'message'       => $request->message,
            'file_path'     => $filePath,
            'status'        => 'pending',
        ]);

        return response()->json($justification, 201);
    }

    // Student submits a justification
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'message'  => 'required|string',
            'file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('justifications', 'public');
        }

        $justification = Justification::create([
            'student_id' => $student->id,
            'type'       => 'justification',
            'message'    => $request->message,
            'file_path'  => $filePath,
            'status'     => 'pending',
        ]);

        return response()->json($justification, 201);
    }

    // Admin: list all justifications (filterable by status)
    public function index(Request $request)
    {
        $query = Justification::with('student');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    // Admin: approve or reject
    public function updateStatus(Request $request, Justification $justification)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $justification->update(['status' => $request->status]);

        return response()->json([
            'message'       => 'Justification updated',
            'justification' => $justification,
        ]);
    }

    public function showFile(Justification $justification)
    {
        if (!$justification->file_path) {
            return response()->json(['message' => 'No file attached to this justification.'], 404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($justification->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $fullPath = $disk->path($justification->file_path);
        $mimeType = $disk->mimeType($justification->file_path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($justification->file_path).'"',
        ]);
    }
}
