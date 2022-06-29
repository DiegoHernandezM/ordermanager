<?php

namespace App\Http\Middleware;

use Closure;

class BasicAuthPDA
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
        $userName = config('thirdparty.pda.username');
        $password = config('thirdparty.pda.password');
        if ($userName == $request->getUser() && $password == $request->getPassword()) {
            return $next($request);
        }

        return response('Unauthenticated.', 401, ['WWW-Authenticate' => 'Basic']);
    }
}
