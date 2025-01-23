<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ComingSoon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
                    return $next($request);

        $allowedRoutes = ['login', 'admin'];

        if ( ! env('COMING_SOON') || auth()->check() || in_array($request->path(), $allowedRoutes)) {
            return $next($request);
        }
    
        return response()->view('coming-soon');
    }

}
