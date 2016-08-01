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

    //which can be TaskBranch,
    public function room(){
        return $this->belongsTo('App\ChatRoom');
    }

    public function sender(){
        // 1 participant is one user
        return $this->belongsTo('App\ChatParticipant');
    }

    public function states(){
        // 1 participant is one user
        return $this->hasMany('App\ChatMessageState');
    }

}
