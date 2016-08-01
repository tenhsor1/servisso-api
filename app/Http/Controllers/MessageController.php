<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Notification;
use App\Message;
use App\Branch;
use App\TaskBranch;

class MessageController extends Controller
{

    public function __construct(){
        parent::__construct();
        $this->middleware('jwt.auth:user', ['only' => ['index',
                                                        'indexTaskBranch',
                                                        'store',
                                                        'show',
                                                        'update',
                                                        'destroy',
                                                        ]]);
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
        $messages = Message::orderByCustom($request)
                        ->get();
        return response()->json(['data' => $messages], 200);
    }

    public function indexTaskBranch(Request $request, $taskId, $taskBranchId){
        $userRequested = \Auth::User();
        $taskBranch = TaskBranch::where(['id' => $taskBranchId])->first();
        if(!$taskBranch){
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }
        if($taskBranch->task->user_id != $userRequested->id){
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }

        $tbComplex = $taskBranch->with(['messages' => function($query){
            $query->with('sender');
            $query->with('receiver');
        }])
        ->where(['id' => $taskBranchId])
        ->first();
        $messages = $tbComplex['messages'];

        return response()->json(['data' => $messages], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userRequested = \Auth::User();

        $errorResponse = Message::validatePayloadStore($request);

        if($errorResponse instanceof JsonResponse){
            return $errorResponse;
        };
        $type = $request->type;
        $message = new Message;
        switch ($type) {
            case Message::BRANCH_TO_TASK:
                $message = $this->messageBranchToTask($message, $userRequested, $request);
                break;
            case Message::USER_TO_TASK:
                $message = $this->messageUserToTask($message, $userRequested, $request);
                break;

            default:
                $response = ['error' => 'Unimplemented type', 'code' => 422];
                $message = response()->json($response, 422);
                break;
        }

        if($message instanceof JsonResponse){
            return $message;
        };
        $message->message = $request->message;

        if($message->save()){
            $response = ['data' => $message, 'code' => 200, 'message' => 'Message was created succefully'];
            return response()->json($response,200);
        }

        $response = ['code' => 500, 'message' => 'Something wrong happened, please contact support'];
        return response()->json($response,500);
    }

    private function messageBranchToTask($message, $userRequested, $request){
        $taskBranch = TaskBranch::where(['id' => $request->object_id])->first();

        if($taskBranch->branch->company->user->id != $userRequested->id){
            $response = ['error' => 'Unauthorized', 'code' => 403];
            \Log::error(sprintf("User: %s requested send a message for task branch: %s. He is not the branch owner. Real owner %s",
                                $userRequested->id, $request->object_id, $taskBranch->branch->company->id));
            return response()->json($response,403);
        }

        $message->sender_id = $taskBranch->branch->id;
        $message->sender_type = Message::MESSAGE_SENDERS['branch'];
        $message->object_id = $request->object_id;
        $message->object_type = Message::MESSAGE_OBJECTS['task_branch'];
        $message->receiver_id = $taskBranch->task->user->id;
        $message->receiver_type = Message::MESSAGE_RECEIVERS['user'];

        return $message;
    }

    private function messageUserToTask($message, $userRequested, $request){
        $taskBranch = TaskBranch::where(['id' => $request->object_id])->first();

        if($taskBranch->task->user_id != $userRequested->id){
            $response = ['error' => 'Unauthorized', 'code' => 403];
            \Log::error(sprintf("User: %s requested send a message for task: %s. He is not the task owner. Real owner %s",
                                $userRequested->id, $request->object_id, $taskBranch->task->user_id));
            return response()->json($response,403);
        }

        $message->sender_id = $userRequested->id;
        $message->sender_type = Message::MESSAGE_SENDERS['user'];
        $message->object_id = $request->object_id;
        $message->object_type = Message::MESSAGE_OBJECTS['task_branch'];
        $message->receiver_id = $taskBranch->branch_id;
        $message->receiver_type = Message::MESSAGE_RECEIVERS['branch'];

        return $message;
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

    public function validateBranchToTask($request){

    }
}
