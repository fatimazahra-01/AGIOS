<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\JustificationController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',   [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});

// ─── Device scan ──────────────────────────────────────────────────────────────
Route::post('scan', [AttendanceController::class, 'scan']);

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // 🔒 ACCÈS RÉSERVÉ : ADMIN & PROFESSEUR
    // On utilise ton middleware 'role' (avec la modif pour accepter plusieurs rôles si possible)
    // Sinon, on peut enchaîner les vérifications.
    Route::middleware('role:admin,professor')->group(function () {
        Route::get('attendance',               [AttendanceController::class, 'index']);
        Route::get('attendance/realtime',      [AttendanceController::class, 'realtime']);
        Route::get('students',                 [StudentController::class, 'index']);
        Route::get('students/{student}',       [StudentController::class, 'show']);
        Route::get('students/{student}/attendance', [AttendanceController::class, 'studentHistory']);
        Route::get('justifications',           [JustificationController::class, 'index']);
    });

    // 🔒 ACCÈS RÉSERVÉ : ÉTUDIANT UNIQUEMENT (pour soumettre ses propres justifications)
    Route::middleware('role:student')->group(function () {
        Route::post('students/{student}/justifications', [JustificationController::class, 'store']);
    });

    // 🔒 ACCÈS RÉSERVÉ : ADMIN UNIQUEMENT
    Route::middleware('role:admin')->group(function () {
        Route::post('students',              [StudentController::class, 'store']);
        Route::put('students/{student}',     [StudentController::class, 'update']);
        Route::delete('students/{student}',  [StudentController::class, 'destroy']);

        Route::patch('justifications/{justification}/status', [JustificationController::class, 'updateStatus']);

        Route::get('config',    [ConfigController::class, 'show']);
        Route::put('config',    [ConfigController::class, 'update']);
    });
});
