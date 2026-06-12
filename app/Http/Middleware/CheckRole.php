<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (!$request->user() || $request->user()->rol !== $role) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción'
            ], 403);
        }

        return $next($request);
    }
}
