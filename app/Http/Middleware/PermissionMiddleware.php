<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
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
