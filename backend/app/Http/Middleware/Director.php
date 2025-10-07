<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Director
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'directeur') {
            return $next($request);
        }
        // Redirect to main dashboard or show 403 error
        return redirect('/dashboard')->with('error', 'Accès refusé. Vous n\'avez pas les permissions de directeur.');
    }
}
