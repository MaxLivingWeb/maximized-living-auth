<?php

namespace App\Http\Middleware;

use App\Helpers\CognitoHelper;
use Closure;

class CaptureRedirectURI
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
        if($request->has('redirect_uri')) {
            $url = $request->input('redirect_uri');
            $cognito = new CognitoHelper();
            if($cognito->checkCallbackUrl($url)) {
                // Save Redirect uri to session
                session()->put('redirect_uri', $url);
            }
        }

        return $next($request);
    }
}
