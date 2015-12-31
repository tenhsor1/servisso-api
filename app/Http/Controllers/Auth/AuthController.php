<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function authenticate(Request $request, $role="user")
    {

        $role = strtoupper($role);
        if($role == 'USER'){
            $model = 'App\User';
        }else if($role == 'PARTNER'){
            $model = 'App\Partner';
        }else if($role == 'ADMIN'){
            $model = 'App\Admin';
        }else{
            $model = 'App\User';
            $role = 'USER';
        }

        \Config::set('auth.model', $model);
        $credentials = $request->only('email', 'password');
        $extraClaims = ['role'=>$role];
        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials, $extraClaims)) {
                return response()->json(['error' => 'Invalid Credentials', 'code'=> 403], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'Could not create token', 'code'=> 500], 500);
        }
        // if no errors are encountered we can return a JWT
        $response = ['data'=> compact('token')];

        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
