<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Sms;

class SmsController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update','destroy']]);
        $this->middleware('default.headers');
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
    public function store(Requests\SmsStoreRequest $request)
    {
        $sms = new Sms;

        $sms->message = $request->input('message');
        $sms->to = $request->input('to');
        $sms->service_id = $request->input('service_id');

        $sms->save();
        return response()->json(['data'=>$sms], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sms = Sms::find($id);
        if($sms){
            return response()->json(['data'=>$sms], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\t exist'
                            , 'code' => 404
                            ];
            return response()->json($errorJSON, 404);
        }
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
    public function update(Requests\SmsUpdateRequest $request, $id)
    {

        $sms = Sms::find($id);
        if($sms){
            if($request->input('message'))
            $sms->message = $request->input('message');
            if($request->input('to'))
                $sms->to = $request->input('to');
            $sms->save();
            return response()->json(['data'=>$sms], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404
                            ];
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
        $sms = Sms::find($id);
        if($sms){
            $sms->delete();
            $respDelete = ['message'=> 'Sms deleted correctly'];
            return response()->json(['data'=>$respDelete], 200);
        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404];
            return response()->json($errorJSON, 404);
        }
    }
}
