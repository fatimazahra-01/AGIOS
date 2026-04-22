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
            'late_after_minutes'      => (int) Cache::get('config.late_after_minutes', 10),
            'absent_after_minutes'    => (int) Cache::get('config.absent_after_minutes', 30),
            'seuil_warn'              => (int) Cache::get('config.seuil_warn', 3),
            'seuil_crit'              => (int) Cache::get('config.seuil_crit', 5),
            'notif'                   => (bool) Cache::get('config.notif', true),
            'email'                   => (bool) Cache::get('config.email', true),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'session_start'        => 'sometimes|string',
            'late_after_minutes'   => 'sometimes|integer|min:1',
            'absent_after_minutes' => 'sometimes|integer|min:1',
            'seuil_warn'           => 'sometimes|integer|min:1',
            'seuil_crit'           => 'sometimes|integer|min:1',
            'notif'                => 'sometimes|boolean',
            'email'                => 'sometimes|boolean',
        ]);

        $keys = [
            'session_start', 'late_after_minutes', 'absent_after_minutes',
            'seuil_warn', 'seuil_crit', 'notif', 'email'
        ];

        foreach ($request->only($keys) as $key => $value) {
            Cache::forever("config.$key", $value);
        }

        return response()->json(['message' => 'Configuration updated']);
    }
}
