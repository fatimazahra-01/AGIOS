<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Admin/Professor: list all students
    public function index()
    {
        return response()->json(Student::with(['attendances'])->get());
    }

    // Admin: register a new student
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'class'          => 'required|string',
            'card_id'        => 'required|string|unique:students',
            'fingerprint_id' => 'nullable|integer|unique:students',
        ]);

        $student = Student::create($request->all());
        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        return response()->json($student->load('attendances', 'justifications'));
    }

    public function update(Request $request, Student $student)
    {
        $student->update($request->all());
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'Student deleted']);
    }
}
