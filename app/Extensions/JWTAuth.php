<?php

namespace App\Extensions;
use Tymon\JWTAuth\JWTAuth as TymonJWTAuth;
/**
* Extension from Tymon JWTAuth Handler, for support custom auth based on roles
*/
class JWTAuth extends TymonJWTAuth
{
    /**
     * Authenticate a user via a token.
     *
     * @param mixed $token
     *
     * @return mixed
     */
    public function authenticate($token = false)
    {

        $id = $this->getPayload($token)->get('sub');
        $role = $this->getPayload($token)->get('role');

        $role = strtoupper($role);
        if($role == 'USER'){
            $model = 'App\User';
        }else if($role == 'PARTNER'){
            $model = 'App\Partner';
        }else if($role == 'ADMIN'){
            $model = 'App\Admin';
        }else{
            $model = 'App\User';
        }

        $modelCompare = \Config::get('auth.model');
        if($modelCompare != $model){
            return false;
        }

        if (! $this->auth->byId($id)) {
            return false;
        }
        $user = $this->auth->user();
        if($user)
            $user->roleAuth = $role;
        return $user;
    }

    /**
     * Find a user using the user identifier in the subject claim.
     *
     * @param bool|string $token
     *
     * @return mixed
     */
    public function toUser($token = false)
    {
        if (! $user = $this->authenticate($token)) {
            return false;
        }
        return $user;
    }
}
