<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:admin')
     *                  ->middleware('role:super_admin,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Guard: Reject unauthenticated users immediately
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // 2. Proceed if user possesses the required role
        if ($user->hasRole($roles)) {
            return $next($request);
        }

        // 3. For API or JSON requests, return strict 403 Forbidden without HTML redirection
        if ($request->expectsJson()) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        // 4. UX Enhancement: Gracefully redirect Admin/Reviewer if accidentally accessing participant-only routes
        if (in_array('participant', $roles, true)) {
            $redirectRoute = match ($user->role) {
                'super_admin', 'admin' => 'admin.dashboard',
                'reviewer'             => 'reviewer.dashboard',
                default                => null,
            };

            if ($redirectRoute) {
                return redirect()->route($redirectRoute)
                    ->with('error', 'Halaman tersebut khusus untuk akun Peserta. Anda telah dialihkan ke dashboard Anda.');
            }
        }

        // 5. Default Security: Strict HTTP 403 Forbidden
        abort(403, 'Unauthorized. You do not have the required role.');
    }
}
