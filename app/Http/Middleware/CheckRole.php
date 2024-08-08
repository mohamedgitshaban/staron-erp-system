<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user is authenticated and has finance access
        if ($user && $user->financeaccess == 1) {
            return $next($request);
        }

        // Return unauthorized response
        return response()->json(['message' => 'Unauthorized',"status"=>Response::HTTP_UNAUTHORIZED], 200);
    }
}
