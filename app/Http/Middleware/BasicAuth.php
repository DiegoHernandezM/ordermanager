<?php
namespace App\Http\Middleware;
use Closure;
class BasicAuth
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
        $userName = config('thirdparty.mercaderias.username');
        $password = config('thirdparty.mercaderias.password');
        $userName2 = config('thirdparty.itapps.password');
        $password2 = config('thirdparty.itapps.password');
        if ($userName == $request->getUser() && $password == $request->getPassword()) {
            return $next($request);
        }

        else if ($userName2 == $request->getUser() && $password2 == $request->getPassword()) {
            return $next($request);
        }

        return response('Unauthenticated.', 401, ['WWW-Authenticate' => 'Basic']);
    }
}
