<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || $token !== config('app.admin_token')) {
            return response()->json([
                'success' => false,
                'error'   => 'UNAUTHORIZED',
                'message' => 'Token invalide ou manquant.',
            ], 401);
        }

        return $next($request);
    }
}
