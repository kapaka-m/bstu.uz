<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        // The apanel role has full-control and bypasses all checks
        if ($user->hasRole('apanel')) {
            return $next($request);
        }

        $permissions = explode('|', $permission);
        foreach ($permissions as $p) {
            if ($user->hasPermission(trim($p))) {
                return $next($request);
            }
        }

        return $this->errorResponse('Unauthorized. Missing required permission.', 403);
    }
}
