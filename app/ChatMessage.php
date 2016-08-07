<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\ChatMessageState;

class ChatMessage extends ServissoModel
{
    const READ_STATE = 'READ';
    const STATES = [ChatMessage::READ_STATE];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_messages';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
                          'created_at',
                          'updated_at'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
                            'deleted_at'
                        ];

    protected $searchFields = [
        'participant_id',
        'message'
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated'
    ];

    public static function boot(){
      ChatMessage::created(function ($message) {
            $message->addNotification('NEW');
      });
    }

    //which can be TaskBranch,
    public function chatRoom(){
        return $this->belongsTo('App\ChatRoom');
    }

    public function chatParticipant(){
        // 1 participant is one user
        return $this->belongsTo('App\ChatParticipant');
    }

    public function states(){
        // 1 participant is one user
        return $this->hasMany('App\ChatMessageState');
    }

    public function createState($chat_participant_id, $state){
        $messageState = new ChatMessageState;
        $messageState->chat_message_id = $this->id;
        $messageState->chat_participant_id = $chat_participant_id;
        $messageState->state = $state;
        return $messageState->save();
   }

    public function addNotification($verb){
        foreach ($this->chatRoom->participants as $key => $participant) {
            $notification = new Notification;
            $notification->receiver_id = $participant->user_id;
            $notification->object_id = $this->id;
            $notification->object_type = Notification::MESSAGE_RELATION;

            $notification->sender_id = $this->chatParticipant->user_id;
            $notification->sender_type = Notification::USER_HIDDEN_RELATION;
            if($this->chatParticipant->object_id){
                $notification->sender_id = $this->chatParticipant->object_id;
                $notification->sender_type = $this->chatParticipant->object_type;
            }

            $notification->verb = $verb;
            $notification->type = 1;
            $chatRoom = $this->ChatRoom;
            $chatName = '';
            foreach ($chatRoom->participants as $participant) {
              if($participant->user_id != $this->chatParticipant->user_id){
                if($participant->object_id){
                  $chatName .= substr($participant->object->company->name, 0, 20) . ', ';
                }else{
                  $chatName .= substr($participant->user->id, 0, 20);
                }
              }
            }
            $chatName = rtrim($chatName, ',');
            $chatName .= ' - '.$chatRoom->name;
            $task = null;

            $notification->extra = json_encode([
                'chat_room_id' => $chatRoom->id,
                'chat_name' => $chatName,
                'id_1' => $chatRoom->object->task_id,
                'id_2' => $chatRoom->object->id,
                'owner' => $chatRoom->object->task->user_id,
            ]);

            $notification->save();
        }
    }
}
