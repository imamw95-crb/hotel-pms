<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @param  string  $permission
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (! auth()->check()) {
            abort(401, 'Unauthorized');
        }

        $permissions = array_map('trim', explode(',', $permission));

        foreach ($permissions as $perm) {
            if (auth()->user()->hasPermission($perm)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource');
    }
}
