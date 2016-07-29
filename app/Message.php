<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use App\Notification;

use Validator;

class Message extends ServissoModel
{
    const BRANCH_TO_TASK  = 'branch_to_task';
    const USER_TO_TASK    = 'user_to_task';
    const USER_TO_BRANCH  = 'user_to_branch';
    const BRANCH_TO_USER  = 'branch_to_user';

    const MESSAGE_OBJECTS = ['task_branch' => 'App\TaskBranch'];
    const MESSAGE_SENDERS = ['user' => 'App\User', 'branch' => 'App\Branch'];
    const MESSAGE_RECEIVERS = ['user' => 'App\User', 'branch' => 'App\Branch'];

    const MESSAGE_TYPES = [Message::BRANCH_TO_TASK, Message::USER_TO_TASK, Message::USER_TO_BRANCH, Message::BRANCH_TO_USER];
    const MESSAGE_TYPES_OBJECT = [Message::BRANCH_TO_TASK, Message::USER_TO_TASK];
    const MESSAGE_TYPES_TO_USER = [Message::BRANCH_TO_USER];
    const MESSAGE_TYPES_TO_BRANCH = [Message::USER_TO_BRANCH];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';
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
        'status',
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

  public static function boot()
  {
  }

  public function addNotification(){
    /*$this->id;
    Notification::SERVICE_RELATION;*/
    $receivers = $this->getOwnerBranch();
    foreach ($receivers as $key => $receiver) {
      $notification = new Notification;
      $notification->receiver_id = $receiver->id;
      $notification->object_id = $this->id;
      $notification->object_type = Notification::SERVICE_RELATION;
      $notification->sender_id = $this->userable_id;
      $notification->sender_type = $this->userable_type;
      $notification->verb = 'NEW';
      $notification->save();
    }
  }

  public function sender(){
      return $this->morphTo();
  }

  public function receiver(){
      return $this->morphTo();
  }

  public function object(){
      return $this->morphTo();
  }

  public function notification(){
      return $this->morphTo();
  }

  public static function validatePayloadStore($request){
        $v = Validator::make($request->all(), Message::getRules(), Message::getMessages());

        $v->sometimes('object_id', 'required|exists:task_branches,id', function($input){
            return in_array($input->type, Message::MESSAGE_TYPES_OBJECT);
        });

        /*$v->sometimes('sender_id', 'required|exists:branches,id', function($input){
            return in_array($input->type, Message::MESSAGE_TYPES_OBJECT);
        });*/

        $v->sometimes('receiver_id', 'required|exists:branches,id', function($input){
            return in_array($input->type, Message::MESSAGE_TYPES_TO_BRANCH);
        });

        $v->sometimes('receiver_id', 'required|exists:users,id', function($input){
            return in_array($input->type, Message::MESSAGE_TYPES_TO_USER);
        });

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }
        return true;
  }

    public static function getRules(){
        $rules = [
            'message' => ['required','max:500', 'min:1'],
            'type' => ['required', 'in:'.implode(',', Message::MESSAGE_TYPES)],
            'object_id' => ['numeric'],
            'sender_id' => ['numeric'],
            'receiver_id' => ['numeric'],
        ];

        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje debe tener máximo :max caracteres',
            'message.min' => 'El mensaje debe tener minimo :min caracteres',
            'object_id.numeric' => 'El id del objeto debe de ser un entero',
            'sender_id.numeric' => 'El id del emisor debe de ser un entero',
            'receiver_id.numeric' => 'El id del receptor debe de ser un entero',
            'type.required' => 'El tipo de mensaje es requerido',
            'type.in' => 'El mensaje debe de ser de tipo: ' . implode(',', Message::MESSAGE_TYPES),
        ];

        return $messages;
    }

    /**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: services?search=test&searchFields=description,company,address)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('description')){
        $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
            $where = "where";
            $whereHas = "whereHas";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'message':
                        //search by the message
                        $query->$where('messages.message', 'LIKE', '%'.$search.'%');
                        break;
                    case 'id':
                        //search for the address of the branch related to the service
                        $query->$where('messages.id', '=', $search);
                        break;
                    case 'status':
                        $query->$where('messages.status', '=', $search);
                        break;
                }
                $where = "orWhere";
                $whereHas = "orWhereHas";
            }
        }
        return $query;
    }

    /**
     * Used for search between a end and a start, based on query parameters passed to the
     * request (example: services?start=2015-11-19&end=2015-12-31&betweenFields=updated,created)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'betweenFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeBetweenBy($query, $request, $defaultFields=array('created')){
        $fields = $this->betweenParametersAreValid($request);
        if($fields){

            $start = null;
            if($request->get('start'))
              $start = $request->get('start') . " 00:00:00";
            $end = null;
            if($request->get('end'))
              $end = $request->get('end') . " 23:59:59";

            $where = "where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'created':
                        //search depending on the creation time
                        if($start)
                            $query->$where('messages.created_at', '>=', $start);
                        if($end)
                            $query->$where('messages.created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->$where('messages.updated_at', '>=', $start);
                        if($end)
                            $query->$where('messages.updated_at', '<=', $end);
                        break;
                }
            }
        }
        return $query;
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
                        $query->orderBy('messages.created_at', $orderType);
                        break;

                    case 'updated':
                        $query->orderBy('messages.updated_at', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}
