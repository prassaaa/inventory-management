<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            return redirect()->route('login');
        }

        // Pastikan metode hasPermissionTo tersedia
        if (!method_exists(Auth::user(), 'hasPermissionTo')) {
            abort(500, 'The HasRoles trait has not been applied to the User model.');
        }

        try {
            if (!Auth::user()->hasPermissionTo($permission)) {
                abort(403, 'Unauthorized action.');
            }
        } catch (\Exception $e) {
            abort(500, 'Error checking permissions: ' . $e->getMessage());
        }

        return $next($request);
    }
}