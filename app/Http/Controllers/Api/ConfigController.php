<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    public function show()
    {
        return response()->json([
            'session_start'           => Cache::get('config.session_start', '08:00:00'),
            'late_after_minutes'      => Cache::get('config.late_after_minutes', 10),
            'absent_after_minutes'    => Cache::get('config.absent_after_minutes', 30),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'session_start'        => 'sometimes|date_format:H:i:s',
            'late_after_minutes'   => 'sometimes|integer|min:1',
            'absent_after_minutes' => 'sometimes|integer|min:1',
        ]);

        foreach ($request->only('session_start', 'late_after_minutes', 'absent_after_minutes') as $key => $value) {
            Cache::forever("config.$key", $value);
        }

        return response()->json(['message' => 'Configuration updated']);
    }
}
