<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth('api')->user();

        // in_array vérifie si le rôle de l'utilisateur est présent dans la liste fournie
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden: insufficient permissions.',
                'your_role' => $user ? $user->role : 'none',
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}
