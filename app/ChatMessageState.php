<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessageState extends ServissoModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_message_states';
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
        'state',
        'chat_room_id',
        'participant_id'
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated'
    ];

    //which can be TaskBranch,
    public function message(){
        return $this->belongsTo('App\ChatMessage');
    }

    public function participant(){
        // 1 participant is one user
        return $this->belongsTo('App\ChatParticipant');
    }

}
