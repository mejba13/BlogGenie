<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/');
        }

        // Check if the authenticated user's role is not 'admin'
        if (Auth::user()->role !== $role) {
            // Redirect to homepage if the user does not have the 'admin' role
            return redirect('/');
        }

        // Continue with the request if the user is admin
        return $next($request);
    }
}
