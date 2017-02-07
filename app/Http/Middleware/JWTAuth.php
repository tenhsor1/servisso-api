<?php

namespace App\Http\Middleware;

use Closure;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuth extends BaseJWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String with roles accepted to authenticate (divided by a pipe '|')
     * @return mixed
     */
    public function handle($request, \Closure $next, $roles='user')
    {

		/* Auth field exceptions, validacion para saltar autenticacion  */
		if( ($request->task_from_bot) && $request->exc_key ){
			if($request->exc_key == \Config::get('app.EXCEPTION_KEY'))
				return $next($request);
		}		

        if (! $token = $this->auth->setRequest($request)->getToken()) {
            return $this->respond('tymon.jwt.absent', ['error' => 'token_not_provided','code' => 401], 401);
        }

        $rolesStr = strtoupper($roles);
        $roles = explode('|', $rolesStr);
        $numRolesRemaining = count($roles);
        foreach ($roles as $role) {
            $numRolesRemaining--;

            if($role == 'USER'){
                $model = 'App\User';
            }else if($role == 'ADMIN'){
                $model = 'App\Admin';
            }else{
                $model = 'App\User';
            }

            \Config::set('auth.model', $model);

            try {
                $user = $this->auth->authenticate($token);
                if($user)
                    $user->roleAuth = $role;
            } catch (TokenExpiredException $e) {
                return $this->respond('tymon.jwt.expired', ['error' => 'token_expired','code' => $e->getStatusCode()], $e->getStatusCode(), [$e]);
            } catch (JWTException $e) {
                return $this->respond('tymon.jwt.invalid', ['error' => 'token_invalid','code' => $e->getStatusCode()], $e->getStatusCode(), [$e]);
            }

            if (! $user) {
                if($numRolesRemaining == 0){
                    return $this->respond('tymon.jwt.user_not_found', 'Unauthorized', 403);
                }
            }else{
                $this->events->fire('tymon.jwt.valid', $user);
                return $next($request);
            }
        }
    }
}

