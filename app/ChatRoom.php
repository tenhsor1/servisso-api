<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use App\ChatMessage;

use Illuminate\Database\Eloquent\SoftDeletes;

use Validator;

class ChatRoom extends ServissoModel
{
    const CHAT_OBJECTS = ['task_branch' => 'App\TaskBranch', 'chat_room' => 'App\ChatRoom'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_rooms';
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
        'id',
        'name'
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated',
        'last',
    ];

  /*public static function boot()
  {
      Message::created(function ($message) {
            $message->addNotification('NEW');
      });
  }*/

  //which can be TaskBranch,
  public function object(){
      return $this->morphTo();
  }

  public function messages(){
    return $this->hasMany('App\ChatMessage');
  }

  public function latestMessage(){
   return $this->hasOne('App\ChatMessage')->latest();
  }

  public function participants(){
    return $this->hasMany('App\ChatParticipant');
  }

  public static function validatePayloadStore($request){
        $v = Validator::make($request->all(), ChatRoom::getRules(), ChatRoom::getMessages());

        $v->sometimes('object_id', 'required|exists:task_branches,id', function($input){
            return $input->object_type == ChatRoom::CHAT_OBJECTS['task_branch'];
        });

        $v->sometimes('object_id', 'required|exists:chat_rooms,id', function($input){
            return $input->object_type == ChatRoom::CHAT_OBJECTS['chat_room'];
        });

        if($v->fails()){
            $response = json_encode($v->errors());
            abort(400, $response);
            return false;
            //return response()->json($response,400);
        }
        return true;
    }

    public static function getRules(){
        $rules = [
            'message' => ['required','max:500', 'min:1'],
            'object_type' => ['required', 'in:' . implode(',', ChatRoom::CHAT_OBJECTS)]
        ];

        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje debe tener máximo :max caracteres',
            'message.min' => 'El mensaje debe tener minimo :min caracteres',
            'object_id.numeric' => 'El id del objeto debe de ser un entero',
            'object_type.required' => 'El tipo del objeto es requerido',
            'object_type.in' => 'El tipo del objeto debe de ser: '.implode(',', ChatRoom::CHAT_OBJECTS),
        ];

        return $messages;
    }

    public static function validatePayloadUpdate($request){
        $v = Validator::make($request->all(), ChatRoom::getUpdateRules(), ChatRoom::getUpdateMessages());

        if($v->fails()){
            $response = json_encode($v->errors());
            abort(400, $response);
            return false;
            //return response()->json($response,400);
        }
        return true;
    }

    public static function getUpdateRules(){
        $rules = [
            'state' => ['in:' . implode(',', ChatMessage::STATES)]
        ];

        return $rules;
    }

    public static function getUpdateMessages(){
        $messages = [
            'state.in' => 'El tipo del estado debe de ser: '.implode(',', ChatMessage::STATES),
        ];

        return $messages;
    }

    /**
     * Used for ordering the result of a get request
     * (example: services?orderBy=created,updated&orderTypes=ASC,DESC)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeOrderByCustom($query, $request){
        $orderFields = $this->orderByParametersAreValid($request);
        if($orderFields){
            $orderTypes = explode(',', $request->input('orderTypes'));
            $cont=0;
            foreach ($orderFields as $orderField) {
                $orderType = $orderTypes[$cont] ? $orderTypes[$cont] : 'DESC';
                switch ($orderField) {
                    case 'created':
                        $query->orderBy('chat_rooms.created_at', $orderType);
                        break;
                    case 'last':
                        $query->orderBy('cm.created_at', $orderType);
                        break;

                    case 'updated':
                        $query->orderBy('chat_rooms.updated_at', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}
