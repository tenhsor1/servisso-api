<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\NotificationEvent;

class Notification extends ServissoModel
{
    const SERVICE_RELATION = 'App\Service';
    const USER_RELATION = 'App\User';
    const GUEST_RELATION = 'App\Guest';
    const MESSAGE_RELATION = 'App\ChatMessage';

    const NOTIFICATION_OBJECTS = ['App\Service', 'App\TaskBranch'];
    const NOTIFICATION_OBJECTS_ALIAS = ['Service', 'TaskBranch'];
    const NOTIFICATION_OBJECTS_MAP = [
                                        'Service' => 'App\Service',
                                        'TaskBranch' => 'App\TaskBranch',
                                        'TaskBranchQuote' => 'App\TaskBranchQuote'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [  'branch_id'
                            , 'sender_id'
                            , 'sender_type'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [   'object_type'
                            , 'sender_type'
                            , 'deleted_at'
                            , 'updated_at'
                        ];

    protected $searchFields = [
        'sender',
        'type',
        'open',
        'read',
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated'
    ];

    public function receiver(){
        //1 service is related to one branch
        return $this->belongsTo('App\User');
    }

    public function sender(){
      return $this->morphTo();
    }

    public function object(){
      return $this->morphTo();
    }

    public static function boot()
    {
        //publish to redis to the receiver id the information needed by the notification
        Notification::created(function ($notification) {
            $eventNotification = new NotificationEvent($notification);
            \Event::fire($eventNotification);
        });
    }

    public function toArray(){
        return [
            'id'        => $this->id,
            'object'    => $this->object ? $this->object->toArray() : null,
            'object_type'    => $this->object_type,
            'sender'    => $this->sender ? $this->sender->toArray() : null,
            'verb'      => $this->verb,
            'extra'     => $this->extra,
            'created'   => $this->created_at->format('Y-m-d\TH:i:s\Z'),
            'is_open'   => $this->is_open ? true : false,
            'is_read'   => $this->is_read ? true : false,
            'type'      => $this->type
        ];
    }

    public static function getRules(){
        $rules = [
            'receiver_id' => ['required']
            , 'object_id' => ['required']
            , 'object_type' => ['required']
            , 'sender_id' => ['required']
            , 'sender_type' => ['required']
        ];

        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'receiver_id.required' => 'El receptor es obligatorio'
            , 'object_id.required' => 'El id del objeto de la notificación es obligatoria'
            , 'object_type.required' => 'El tipo del objeto de la notificación es obligatoria'
            , 'sender_id.required' => 'El id del emisor de la notificación es obligatoria'
            , 'sender_type.required' => 'El tipo del emisor de la notificación es obligatoria'
        ];

        return $messages;
    }

    public static function getMultipleRules(){
        $rules = [
            'type' => ['required', 'in:is_open,is_read,Service']
            , 'ids' => ['array']
        ];

        return $rules;
    }

    public static function getMultipleMessages(){
        $messages = [
            'type.required' => 'El tipo de actualización es obligatorio'
            , 'type.in' => 'El tipo debe de ser: [:values]'
            , 'ids.array' => 'La lista de identificadores debe de ser un arreglo'
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
                    case 'sender':
                        //search by the sender id
                        $query->$where('notifications.sender_id', '=', $search);
                        break;
                    case 'type':
                        //search by the address
                        $query->$where('notifications.type', '=', $search);
                        break;
                    case 'open':
                        //search by the address
                        $query->$where('notifications.is_open', '=', $search);
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
                            $query->$where('services.created_at', '>=', $start);
                        if($end)
                            $query->$where('services.created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->$where('services.updated_at', '>=', $start);
                        if($end)
                            $query->$where('services.updated_at', '<=', $end);
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
                        $query->orderBy('created_at', $orderType);
                        break;

                    case 'updated':
                        $query->orderBy('updated_at', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }

    public function scopeLimit($query, $request){
        if($this->limitParametersAreValid($request)){
            $limit = $request->input('limit');
            if($page = $request->input('page')){
                $page = $page - 1;
                $page = $page * $limit;
                $query->skip($page)->take($limit);
            }else{
                $query->take($limit);
            }
        }else{
            //if not limit passed, then just show 2000 results as max
            $query->take(100);
        }
        return $query;
    }
}