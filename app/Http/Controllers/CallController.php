<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Call;

class CallController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update', 'destroy']]);
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
    public function store(Requests\CallStoreRequest $request)
    {
        $call = new Call;

        $call->length = $request->input('length', 0);
        $call->url = $request->input('url', '');
        $call->status = $request->input('status');
        $call->to = $request->input('to');
        $call->from = $request->input('from');
        $call->answered = $request->input('answered');
        $call->service_id = $request->input('service_id');

        $call->save();
        return response()->json(['data'=>$call], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $call = Call::find($id);
        if($call){
            return response()->json(['data'=>$call], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
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
    public function update(Requests\CallUpdateRequest $request, $id)
    {

        $call = Call::find($id);
        if($call){
            if($request->input('to'))
            $call->to = $request->input('to');
            if($request->input('from'))
                $call->from = $request->input('from');
            $call->save();
            return response()->json(['data'=>$call], 200);

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
        $call = Call::find($id);
        if($call){
            $call->delete();
            $respDelete = ['message'=> 'Call deleted correctly'];
            return response()->json(['data'=>$respDelete], 200);
        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404];
            return response()->json($errorJSON, 404);
        }
    }
}
