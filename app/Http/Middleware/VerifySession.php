<?php

namespace App\Http\Middleware;

use Closure;

class VerifySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!session()->has('cognitoSession')) {
            return redirect()->route('home');
        }

        if(!session()->has('username')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
