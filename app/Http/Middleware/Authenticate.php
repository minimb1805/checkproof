<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Check if the request is an API request and user is unauthenticated
        if (Auth::guard('sanctum')->guest()) {
            // Return 401 Unauthorized for API requests
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return $next($request);
    }
}