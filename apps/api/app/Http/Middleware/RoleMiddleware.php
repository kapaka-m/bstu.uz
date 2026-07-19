<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        // The apanel role has full-control and bypasses all checks
        if ($user->hasRole('apanel')) {
            return $next($request);
        }

        $roles = explode('|', $role);
        foreach ($roles as $r) {
            if ($user->hasRole(trim($r))) {
                return $next($request);
            }
        }

        return $this->errorResponse('Unauthorized. Missing required role.', 403);
    }
}
