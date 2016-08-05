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

    public function __construct(){
        parent::__construct();
        $this->middleware('jwt.auth:user');
        $this->middleware('default.headers');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request){

        $userRequested = \Auth::User();
        $notOpen = ChatParticipant::where(['user_id' => $userRequested->id, 'open' => false])->count();

       $data = [
        'not_open' => $notOpen
       ];
        return response()->json(['data' => $data], 200);

    }

    public function indexMessages(Request $request)
    {
        $userRequested = \Auth::User();
        $messages = ChatRoom::orderByCustom($request)
                        ->with('object')
                        ->with('participants')
                        ->with(['latestMessage' => function($q){
                            $q->with(['chatParticipant' => function($q){
                                $q->with('user');
                                $q->with('object');
                            }]);
                        }])
                        ->join('chat_messages AS cm', function($q){
                            $q->on('cm.chat_room_id', '=', 'chat_rooms.id')
                            ->on('cm.updated_at', '=', \DB::raw('(SELECT MAX(cmm.updated_at) FROM chat_messages cmm WHERE cmm.chat_room_id = chat_rooms.id)'));

                        })
                        ->leftJoin('chat_participants as cmp', function($q) use($userRequested){
                            $q->on('chat_rooms.id', '=', 'cmp.chat_room_id')
                                ->on('cmp.user_id', '=', \DB::raw($userRequested->id));
                        })
                        ->leftJoin('chat_message_states AS sread', function($q){
                            $q->on('cm.id', '=', 'sread.chat_message_id')
                                ->on('sread.chat_participant_id', '=', 'cmp.id')
                                ->on('state', '=', \DB::raw("'".ChatMessage::READ_STATE."'"));
                        })
                        ->whereHas('participants', function($q) use ($userRequested){
                            $q->where('user_id', $userRequested->id);
                        })
                        ->select('chat_rooms.id',
                                'chat_rooms.object_id',
                                'chat_rooms.object_type',
                                'chat_rooms.name',
                                \DB::raw('sread.id > 0 AS read'))
                        ->get();
        return response()->json(['data' => $messages], 200);
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

        ChatRoom::validatePayloadStore($request);

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

        $response = ['data' => $message, 'code' => 200, 'message' => 'Message was created succefully'];
        return response()->json($response,200);
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

        ChatRoom::validatePayloadUpdate($request);
        $room = ChatRoom::where(['id' => $id])->first();
        if(!$room){
            abort(404, 'Resource not found');
        }

        $userRequested = \Auth::User();
        $participant = ChatParticipant::where(['user_id' => $userRequested->id, 'chat_room_id' => $id])->first();
        if(!$participant){
            abort(403, 'Unauthorized');
        }

        $roomMessages = ChatRoom::where(['id' => $id])
                            ->with(['messages' => function($query) use($participant){
                                $query->select('chat_messages.*')
                                ->leftJoin('chat_message_states AS s', function($q) use($participant){
                                    $q->on('chat_messages.id', '=', 's.chat_message_id')
                                        ->on('s.chat_participant_id', '=', \DB::raw($participant->id))
                                        ->on('s.state', '=', \DB::raw("'".$request->state."'"));
                                })->whereNull('s.id');
                                //->select('chat_messages.id');

                            }])->first();
        foreach ($roomMessages['messages'] as $message){
            $state = $message->createState($participant->id, $request->state);
        }
        $response = ['data' => $roomMessages, 'code' => 200, 'message' => 'Message was created succefully'];
        return response()->json($response,200);
    }

    public function updateAll(Request $request){
        $userRequested = \Auth::User();
        $message = null;
        if($request->type == 'open'){
            $userRequested->chatParticipants()->update(['open' => true]);
            $message = 'Open status for message updated correctly';
        }
        $response = ['data' => $userRequested, 'code' => 200, 'message' => $message];
        return response()->json($response,200);
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
        $taskBranch = TaskBranch::where(['id' => $request->object_id])->first();

        if(!$taskBranch){
            abort(404, 'Object not found');
        }

        if($taskBranch->chatRoom){
            $participant = ChatParticipant::where([
                                            'chat_room_id' => $taskBranch->chatRoom->id,
                                            'user_id' => $userRequested->id,
                                            ])->first();
            if(!$participant){
                abort(403, 'Unauthorized');
                return false;
            }
            return $this->createMessage($message, $taskBranch->chatRoom, $participant);
        }
        //create a new chat room with it participants based on the task branch object
        $chatRoom = $this->createChatRoom($userRequested, $taskBranch);
        $participants = $chatRoom->participants;
        return $this->createMessage($message, $chatRoom, $participants[0]);
    }

    private function createChatRoom($userRequested, $object){

        $chatRoom = new ChatRoom;

        $class = 'App\TaskBranch';
        if($object instanceof $class){
            $participants = $this->getTaskBranchParticipants($userRequested, $object);
            $chatRoom->name = substr($object->task->description, 0, 12);
            $chatRoom->object_id = $object->id;
            $chatRoom->object_type = $class;
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
            $receiver->user_id = $taskBranch->task->user_id;
            $receiver->object_id = null;
            $receiver->object_type = null;
        }

        if(!$validUser){
            abort(403, 'Unauthorized');
            return false;
        }
        $participants[] = $sender;
        $participants[] = $receiver;
        return $participants;
    }

    private function createMessage($message, $chatRoom, $participant){
        $chatMessage = new ChatMessage;
        $chatMessage->message = $message;
        $chatMessage->chat_room_id = $chatRoom->id;
        $chatMessage->chat_participant_id = $participant->id;
        $chatRoom->participants()->update(['open' => false]);
        $participant->open = true;
        $participant->save();

        if(!$chatMessage->save()){
            abort(500, 'Something went wrong, please contact support');
        }

        return $chatMessage;
    }
}
