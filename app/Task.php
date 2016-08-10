<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use App\Notification;

class Task extends ServissoModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tasks';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['user_id'
                            , 'category_id'
							,'created_at'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
                            'geom',
                            'deleted_at'
                        ];

    protected $searchFields = [
        'id',
        'status',
        'description',
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
      /*Task::created(function ($task) {
        $task->addNotification();
      });*/
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

  public function user(){
      //1 task is made by 1 user
      return $this->belongsTo('App\User');
  }

  public function userHidden(){
        // 1 participant is one user
      return $this->belongsTo('App\UserHidden', 'user_id', 'id');
  }

    public function category(){
        //a task has 1 category
        return $this->belongsTo('App\Category');
    }

  public function userable(){
      return $this->morphTo();
  }


  public function branches()
    {
        return $this->hasMany('App\TaskBranch');
    }

  public function distanceBranches(){
    return $this->hasMany('App\TaskBranch')
      ->join('tasks', 'task_branches.task_id', '=', 'tasks.id')
      ->join('branches', 'task_branches.branch_id', '=', 'branches.id')
      ->whereIn('task_branches.status', TaskBranch::ACCEPTED_STATUSES)
      ->select('task_branches.*', \DB::raw('ST_Distance_Sphere(branches.geom,tasks.geom) as meters_distance'));
  }

  public function openBranches(){
      return $this->hasMany('App\TaskBranch')->where('status', 1);
  }

  public function notifications(){
    return $this->morphMany('App\Task', 'object');
  }

  public function userRates(){
    return $this->morphMany('App\UserRate', 'object');
  }

  public function branchRates(){
    return $this->morphMany('App\BranchRate', 'object');
  }

  public function images(){
    return  $this->hasMany('App\TaskImage');
  }

    public static function getRules(){
        $rules = [
            'description' => ['required','max:500', 'min:30'],
            'category_id' => ['required','exists:categories,id'],
            'date' => ['required','date_format:Y-m-d H:i:s', 'after:yesterday'],
            'delivery_service' => ['required', 'boolean'],
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
        ];

        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'description.required' => 'Descripción es obligatoria',
            'description.max' => 'Descripción debe tener máximo :max caracteres',
            'description.min' => 'Descripción debe tener minimo :min caracteres',
            'category_id.required' => 'La categoria es obligatoria',
            'category.exists' => 'La categoria no existe',
            'date.required' => 'la fecha es obligatoria',
            'date.date_format' => 'la fecha debe de ser en formato Y-m-d H:i:s',
            'date.after' => 'la fecha no puede ser anterior a hoy',
            'delivery_service.required' => 'delivery_service es obligatorio',
            'delivery_service.boolean' => 'delivery_service debe de ser booleano',
            'latitude.required' => 'La latitud es requerida',
            'longitude.required' => 'La longitud es requerida',
            'latitude.regex' => 'La latitud no es valida',
            'longitude.regex' => 'La longitud no es valida',
        ];

        return $messages;
    }

    public function setGeomAttribute($value) {
        //position 0 = longitude, 1 = latitude
        $this->attributes['geom'] = \DB::raw(sprintf("ST_SetSRID(ST_MakePoint(%s, %s), 4326)", $value[0], $value[1]));
    }

    public function getGeomAttribute(){
        return null;
    }

    public function getNeareastBranches($numberBranches=20, $meters=10000){
        return $this->select('branches.*')
            ->join('branches','branches.id','>',\DB::raw('0'))
            ->join('companies', 'branches.company_id', '=', 'companies.id')
            ->where('tasks.id', $this->id)
            ->where('companies.category_id', $this->category_id)
            ->whereRaw('ST_DWithin(tasks.geom::geography, branches.geom::geography, '.$meters. ', false)')
            ->orderBy(\DB::raw('branches.geom <-> tasks.geom'))
            ->take($numberBranches)
            ->get();
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
                    case 'description':
                        //search by the description of the task
                        $query->$where('tasks.description', 'LIKE', '%'.$search.'%');
                        break;
                    case 'id':
                        //search for the address of the branch related to the service
                        $query->$where('tasks.id', '=', $search);
                        break;
                    case 'status':
                        $query->$where('tasks.status', '=', $search);
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
                            $query->$where('tasks.created_at', '>=', $start);
                        if($end)
                            $query->$where('tasks.created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->$where('tasks.updated_at', '>=', $start);
                        if($end)
                            $query->$where('tasks.updated_at', '<=', $end);
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
                        $query->orderBy('tasks.created_at', $orderType);
                        break;

                    case 'updated':
                        $query->orderBy('tasks.updated_at', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}
