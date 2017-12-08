<?php

namespace App\Http\Middleware;

use Closure;

class VerifySetup
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
        if(!session()->has('redirect_uri')) {
            return response(view('error', ['error' => 'Invalid redirect_url']));
        }

        return $next($request);
    }
}
