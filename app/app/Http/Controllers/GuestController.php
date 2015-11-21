<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Guest;
use JWTAuth;
use Validator;

class GuestController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['index', 'update', 'destroy']]);
        $this->middleware('jwt.auth:partner|admin', ['only' => ['show']]);
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
    public function store(Request $request)
    {
        $messages = Guest::getMessages();
        $validation = Guest::getStoreValidations();

        $v = Validator::make($request->all(),$validation,$messages);

        $response = ['error' => $v->messages(), 'code' =>  422];

        //Validate if something failed with the fields passed
        if($v->fails()){
            return response()->json($response,422);
        }

        $guest = Guest::create($request->all());

        if($guest){
            $response = ['data' => $guest,'code' => 200,'message' => 'Guest was created succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the guest','code' => 500];
            return response()->json($response,500);
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
        $guest = Guest::find($id);
        if($guest){
            return response()->json(['data'=>$guest], 200);
        }else{
            $errorJSON = ['error'   => 'Guest not found'
                            , 'code' => 404];
            return response()->json($errorJSON, 404);
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
        $guest = Guest::find($id);
        if($guest){
            $messages = Guest::getMessages();
            $validation = Guest::getUpdateValidations();

            $v = Validator::make($request->all(),$validation,$messages);

            $response = ['error' => $v->messages(), 'code' =>  422];

            //Validate if something failed with the fields passed
            if($v->fails()){
                return response()->json($response,422);
            }

            $attributes = ['name'
                            , 'email'
                            , 'name'
                            , 'address'
                            , 'phone'
                            , 'zipcode'
                        ];
            $this->updateModel($request, $guest, $attributes);
            $save = $guest->save();
            if($save){
                return response()->json(['data'=>$guest], 200);
            }else{
                $response = ['error' => 'It has occurred an error trying to update the guest','code' => 500];
                return response()->json($response,500);
            }
        }else{
             $errorJSON = ['error'   => 'Guest not found'
                            , 'code' => 404];
            return response()->json($errorJSON, 404);
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
        $guest = Guest::find($id);
        if($guest){
            $delete = $guest->delete();
            if($delete){
                $respDelete = ['message'=> 'Guest deleted correctly'];
                return response()->json(['data'=>$respDelete], 200);
            }else{
                $response = ['error' => 'It has occurred an error trying to delete the guest','code' => 500];
                return response()->json($response,500);
            }
        }else{
             $errorJSON = ['error'   => 'Guest not found'
                            , 'code' => 404];
            return response()->json($errorJSON, 404);
        }
    }
}
