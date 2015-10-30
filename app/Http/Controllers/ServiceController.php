<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Service;
use Tymon\JWTAuth\Exceptions\JWTException;

class ServiceController extends Controller
{
    public function __construct(){
        $this->middleware('default.headers');
        $this->apiUrl = \Config::get('app.api_url');
        $this->userTypes = \Config::get('app.user_types');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ServiceStoreRequest $request)
    {
        try{
            $token = \JWTAuth::getToken();
            $user = \JWTAuth::toUser($token);
        }catch(JWTException $e){
            $user = null;
        }
        if($user){
            $userId = $user->id;
            $userType = $this->userTypes['user'];
        }else{
            $userId = $request->input('guest_id');
            $userType = $this->userTypes['guest'];
        }
        if($userId > 0){
            $service = new Service;

            $service->description = $request->input('description');
            $service->branch_id = $request->input('branch_id');
            $service->user_id = $userId;
            $service->user_type = $userType;

            $service->save();
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'Bad request'
                            , 'code' => 422
                            , 'data' => [
                                'guest_id'=> 'Missing the id of the user/guest requesting the service'
                                ]];
            return response()->json($errorJSON, 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $token = \JWTAuth::getToken();
            $user = \JWTAuth::toUser($token);
        }catch(JWTException $e){
            return dd($e);
            $user = null;
        }
        $userId = 0;
        if($user){
            $userId = $user->id;
        }
        $conditions = [ 'id' => $id
                        , 'user_id' => $userId
                        , 'user_type' => $this->userTypes['user']];
        $service = Service::where($conditions)->first();
        if($service){
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'Bad request'
                            , 'code' => 422
                            , 'data' => [
                                'user_id'=> 'The user doesn\'t have this service'
                                ]];
            return response()->json($errorJSON, 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $token = \JWTAuth::getToken();
            $user = \JWTAuth::toUser($token);
        }catch(JWTException $e){
            return dd($e);
            $user = null;
        }
        $userId = 0;
        if($user){
            $userId = $user->id;
        }
        $conditions = [ 'id' => $id
                        , 'user_id' => $userId
                        , 'user_type' => $this->userTypes['user']];
        $service = Service::where($conditions)->first();
        if($service){
            $service->description = $request->input('description');
            $service->save();
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'Bad request'
                            , 'code' => 422
                            , 'data' => [
                                'user_id'=> 'The user doesn\'t have this service'
                                ]];
            return response()->json($errorJSON, 422);
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
        //
    }
}
