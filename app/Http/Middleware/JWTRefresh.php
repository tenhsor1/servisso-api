<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RefreshToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next, $role='user')
    {

        $role = strtoupper($role);
        if($role == 'USER'){
            $model = 'App\User';
        }else if($role == 'PARTNER'){
            $model = 'App\Parner';
        }else if($role == 'ADMIN'){
            $model = 'App\Admin';
        }else{
            $model = 'App\User';
        }

        \Config::set('auth.model', $model);

        $response = $next($request);

        try {
            $newToken = $this->auth->setRequest($request)->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }

        // send the refreshed token back to the client
        $response->headers->set('Authorization', 'Bearer ' . $newToken);

        return $response;
    }
}