<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends ServissoModel
{
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

    public function addNotification($verb){
        foreach ($this->chatRoom->participants as $key => $participant) {
            $notification = new Notification;
            $notification->receiver_id = $participant->user_id;
            $notification->object_id = $this->id;
            $notification->object_type = Notification::MESSAGE_RELATION;

            $notification->sender_id = $this->chatParticipant->user_id;
            $notification->sender_type = Notification::USER_RELATION;
            if($this->chatParticipant->object_id){
                $notification->sender_id = $this->chatParticipant->object_id;
                $notification->sender_type = $this->chatParticipant->object_type;
            }

            $notification->verb = $verb;
            $notification->type = 1;
            $object = $this->chatRoom->object ? $this->chatRoom->object->toArray() : null;
            $notification->extra = json_encode([
                'object' => $object
            ]);

            $notification->save();
        }
    }
}
