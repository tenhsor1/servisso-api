<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ChatRoom;
use App\ChatParticipant;
use App\ChatMessage;
use App\ChatMessageState;
use App\TaskBranch;

class ChatController extends Controller
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
        $userRequested = \Auth::User();

        $errorResponse = ChatRoom::validatePayloadStore($request);

        if($errorResponse instanceof JsonResponse){
            return $errorResponse;
        };

        $type = $request->object_type;
        $message = $request->message;
        switch ($type) {
            case ChatRoom::CHAT_OBJECTS['task_branch']:
                $message = $this->chatTaskBranch($message, $userRequested, $request);
                break;
            case ChatRoom::CHAT_OBJECTS['chat_room']:
                $message = $this->messageBranchToTask($message, $userRequested, $request);
                break;

            default:
                $response = ['error' => 'Unimplemented type', 'code' => 422];
                $message = response()->json($response, 422);
                break;
        }

        //$response = ['code' => 500, 'message' => 'Something wrong happened, please contact support'];
        return response()->json($response,500);
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



    private function chatTaskBranch($message, $userRequested, $request){
        \Log::debug($request->object_id);
        $taskBranch = TaskBranch::where(['id' => $request->object_id])->first();

        if(!$taskBranch){
            abort(404, 'Object not found');
        }

        if($taskBranch->chatRoom){
            return $this->createMessage($message, $taskBranch->chatRoom, $userRequested);
        }
        //create a new chat room with it participants based on the task branch object
        $chatRoom = $this->createChatRoom($userRequested, $taskBranch);
        $participants = $chatRoom->participants;
        return $this->createMessage($message, $chatRoom, $sender, $participants[0]);
    }

    private function createChatRoom($userRequested, $object){

        $chatRoom = new ChatRoom;

        $class = 'App\TaskBranch';
        if($object instanceof $class){
            $participants = $this->getTaskBranchParticipants($userRequested, $object);
            $chatRoom->name = substr($object->task->description, 0, 12);
            $chatRoom->object = $object;
        }

        if(!$participants){
            abort(500, 'Something unexpected happened, please contact support');
            return false;
        }
        $chatRoom->save();
        foreach ($participants as $key => $participant) {
           $chatRoom->participants()->save($participant);
        }
        return $chatRoom;
    }

    private function getTaskBranchParticipants($userRequested, $taskBranch){
        $participants = [];
        $sender = new ChatParticipant;
        $sender->user_id = $userRequested->id;
        $sender->object_id = null;
        $sender->object_type = null;

        $receiver = new ChatParticipant;
        $validUser = false;

        if($userRequested->id == $taskBranch->task->user_id){
            $validUser = true;
            $receiver->user_id = $taskBranch->branch->company->user_id;
            $receiver->object_id = $taskBranch->branch_id;
            $receiver->object_type = ChatParticipant::PARTICIPANT_OBJECTS['branch'];
        }else if($taskBranch->branch->company->user->id == $userRequested->id){
            $validUser = true;
            $sender->object_id = $taskBranch->branch_id;
            $sender->object_type = ChatParticipant::PARTICIPANT_OBJECTS['branch'];
        }

        if(!$validUser){
            abort(403, 'Unauthorized');
            return false;
        }
        $participants[] = $sender;
        $participants[] = $receiver;
        return $participants;
    }

    private function createMessage($message, $chatRoom, $sender){
        $chatMessage = new ChatMessage;
        $chatMessage->message = $message;
        $chatMessage->room = $chatRoom;
        $chatMessage->sender = $sender;
        if(!$chatMessage->save()){
            abort(500, 'Something went wrong, please contact support');
        }
        return $chatMessage;
    }
}
