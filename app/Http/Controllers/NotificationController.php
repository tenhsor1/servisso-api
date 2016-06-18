<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Notification;
use JWTAuth;
use Validator;

class NotificationController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:user');
        $this->middleware('default.headers');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userRequested = \Auth::User();
        if(!$request->input('count')){
            $notifications = Notification::searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->with('object')
                                ->where('receiver_id', '=', $userRequested->id)
                                ->get();
        }else{
            $notifications = Notification::searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->where('receiver_id', '=', $userRequested->id)
                                ->get();
        }

        $count = $notifications->count();

        $response = ['code' => 200,'Count' => $count,'data' => $notifications];
        //Redis::publish(1, 'test');
        return response()->json($response,200);
    }

    public function updateMultiple(Request $request){
        $userRequested = \Auth::User();

        $rules = Notification::getMultipleRules();
        $messages = Notification::getMultipleMessages();

        $validator = Validator::make($request->all(),$rules,$messages);

        if($validator->fails()){
            $response = ['error' => $validator->errors(),'message' => 'Bad request','code' => 400];
            return response()->json($response,400);
        }
        \Log::debug($request->input('type'));
        $result = Notification::whereIn('id', $request->input('ids'))
                                    ->where('receiver_id', $userRequested->id)
                                    ->update(array($request->input('type') => true));
        $response = ['data' => ['count' => $result]
                    ,'code' => 200
                    ,'message' => 'Records updated succesfully'];
        return response()->json($response,200);
    }
}