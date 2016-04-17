<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Encryption\DecryptException;
use App\User;
use App\UserSocial;
use Validator;

class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.refresh', ['only' => ['refresh']]);
        $this->middleware('default.headers');
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

    public function authenticate(Request $request, $role="user")
    {

        $role = strtoupper($role);
        if($role == 'USER'){
            $model = 'App\User';
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
                return response()->json(['error' => 'Email y/o password incorrectos', 'code'=> 403], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'No se pudo crear el token', 'code'=> 500], 500);
        }
        // if no errors are encountered we can return a JWT
        $user = JWTAuth::toUser($token);
        $user->access = $token;

        $response = ['data'=> $user];

        return response()->json($response);
    }

    public function refresh(Request $request)
    {
        $data = ["success"=> true, "Message"=> "El token se generó correctamente"];
        $response = ['data'=> $data];
        return response()->json($response);
    }

    public function socialAuth(Request $request)
    {
        try{
            $values = \Crypt::decrypt($request->input('val'));
        }catch(DecryptException $e){
            $data = ['val' => ["La información no es correcta"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return response()->json($response, 403);
        }

        $rules = UserSocial::getRules();
        $messages = UserSocial::getMessages();

        $validator = Validator::make($values,$rules,$messages);

        if($validator->fails()){
            $response = ['data' => $validator->errors(),'error' => 'Bad request','code' => 400];
            return response()->json($response, 400);
        }

        $user = User::where('email', '=', $values['email'])->first();
        //first check if the user already exist in servisso
        if(!$user){
            $response = ["success"=> false, "message"=> "El usuario no tiene cuenta en Servisso", "data" => $request->all()];
            return response()->json($response);
        }
        $token = $user->loginSocial($values);
        if($token){
            $user->confirmed = true;
            $user->save();
            $user->access = $token;
            $response = ["message" => 'Se autentificó correctamente', "success" => true, "data" => $user];
            return response()->json($response);
        }
        return response()->json(['error' => 'Sucedió un error inesperado', 'code'=> 500], 500);
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
