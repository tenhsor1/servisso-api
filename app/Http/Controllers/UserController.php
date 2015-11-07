<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use JWTAuth;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['except' => ['store']]);
        $this->middleware('default.headers');
        //$this->api_url = \Config::get('app.api_url');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return "index";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\UserStoreRequest $request)
    {
        $newUser = User::create($request->all());

        $extraClaims = ['role'=>'USER'];
        $token = JWTAuth::fromUser($newUser,$extraClaims);
        $reflector = new \ReflectionClass('JWTAuth');
        $newUser->token = $token;
        return response()->json(['data'=>$newUser], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userRequested = \Auth::User();
        //check if the user who requested the resource is the same
        //as the resource been requested
        if($userRequested->id == $id){
            return response()->json(['data'=>$userRequested], 200);
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UserUpdateRequest $request, $id)
    {
        $userRequested = \Auth::User();
        if($userRequested->id == $id){
            $attributes = ['name'
                            , 'email'
                            , 'password'
                            , 'last_name'
                            , 'phone'
                            , 'address'
                            , 'zipcode'
                        ];
            $this->updateModel($request, $userRequested, $attributes);
            $userRequested->save();
            return response()->json(['data'=>$userRequested], 200);
        }else{
             $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userRequested = \Auth::User();
        //check if the user who requested the resource is the same
        //as the resource been requested
        if($userRequested->id == $id){
            $userRequested->delete();
            $respDelete = ['message'=> 'User deleted correctly'];
            return response()->json(['data'=>$respDelete], 200);
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }
}
