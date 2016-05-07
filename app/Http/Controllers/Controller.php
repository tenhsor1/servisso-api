<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function updateModel($request, $model, $attributes){
        foreach ($attributes as $attribute) {
            if($request->has($attribute))
                $model->$attribute = $request->input($attribute);
        }
    }

    protected function checkAuthUser($roles='user'){

        $rolesStr = strtoupper($roles);
        $roles = explode('|', $rolesStr);
        $numRolesRemaining = count($roles);

        foreach ($roles as $role) {
            $unauthorized = false;
            $numRolesRemaining--;

            //based on the role passed, try to get the kind of model for the user
            if($role == 'USER'){
                $model = 'App\User';
            }else if($role == 'ADMIN'){
                $model = 'App\Admin';
            }else{
                $model = 'App\User';
            }

            \Config::set('auth.model', $model);

            //get the token, and based on it, try to get the user object
            try{
                $token = JWTAuth::getToken();
                $user = JWTAuth::toUser($token);
            }catch(TokenExpiredException $e){
                $unauthorized = true;
                $user=null;
            }catch(JWTException $e){
                $user = null;
            }
            //if the token is not valid, then return an array with unauthorized error
            if($unauthorized && $numRolesRemaining == 0){
                return ['code'=> 403, 'error'=>'Unauthorized'];
            }
            //if the user exist, then return it
            if($user){
                $user->roleAuth = $role;
                return $user;

            }else if(!$user && $numRolesRemaining == 0){
                return $user;
            }
        }
    }
}
