<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatParticipant extends ServissoModel
{

    const PARTICIPANT_OBJECTS = ['branch' => 'App\Branch'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_participants';
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
        'user_id',
        'branch_id'
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
    public function object(){
        return $this->morphTo();
    }

    public function user(){
        // 1 participant is one user
        return $this->belongsTo('App\User');
    }

    public function room(){
        // 1 participant is one user
        return $this->belongsTo('App\ChatRoom');
    }

    public function messages(){
        // 1 participant is one user
        return $this->hasMany('App\ChatMessage');
    }
}
