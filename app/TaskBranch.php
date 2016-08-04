<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use App\Notification;
use App\TaskBranchLog;

class TaskBranch extends ServissoModel
{
    const STATUSES = [
        'not_open'      => 0,
        'open'          => 1,
        'interested'    => 2,
        'rejected'      => 3
    ];

    const ACCEPTED_STATUSES = [
        'interested'    => 2
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'task_branches';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['task_id'
                            , 'branch_id'
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
        'task',
        'branch',
		'status'
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
        TaskBranch::created(function ($taskBranch) {
            $taskBranch->addLog('SENT');
            $taskBranch->addNotification('NEW');
        });

        TaskBranch::updating(function ($taskBranch){
            if($taskBranch->status != $taskBranch->getOriginal('status')){
                \Log::info("taskbranch {$taskBranch->id} status: {$taskBranch->getOriginal('status')} just updated to {$taskBranch->status}");
                $taskBranch->addLog('STATUS', $taskBranch->status);
            }

        });
    }

    public function addLog($type, $value=null){
        $log = new TaskBranchLog(['type' => $type, 'value' => $value]);
        $this->logs()->save($log);
    }

    public function addNotification($verb){

        $receiver = $this->getOwnerBranch();
        $notification = new Notification;
        $notification->receiver_id = $receiver->id;
        $notification->object_id = $this->id;
        $notification->object_type = Notification::NOTIFICATION_OBJECTS_MAP['TaskBranch'];
        $notification->sender_id = $this->task->user_id;
        $notification->sender_type = Notification::USER_RELATION;
        $notification->verb = $verb;
        $notification->extra = json_encode([
            'task' => $this->task->toArray(),
            'distance' => $this->getDistance()
        ]);
        $notification->save();
    }

    public function getOwnerBranch(){
      return $this->select('users.id')
            ->join('branches','branches.id','=','task_branches.branch_id')
            ->join('companies','companies.id','=','branches.company_id')
            ->join('users','users.id','=','companies.user_id')
            ->where('task_branches.id', $this->id)
            ->first();
    }

    public function scopeWithDistance($query){
        return $query->join('tasks','task_branches.task_id','=','tasks.id')
            ->join('branches', 'task_branches.branch_id', '=', 'branches.id')
            ->select('task_branches.*', \DB::raw('ST_Distance_Sphere(branches.geom,tasks.geom) as meters_distance'));
    }

    public function getDistance(){
        $result = TaskBranch::select(\DB::raw('ST_Distance_Sphere(branches.geom,tasks.geom) as meters_distance'))
        ->join('tasks','task_branches.task_id','=','tasks.id')
        ->join('branches', 'task_branches.branch_id', '=', 'branches.id')
        ->where('task_branches.id', $this->id)
        ->first();
        return $result->meters_distance;
    }

    public function task(){
        //1 task-branch has one task
        return $this->belongsTo('App\Task');
    }

    public function branch(){
        //a task has 1 category
        return $this->belongsTo('App\Branch');
    }

    public function notifications(){
        return $this->morphMany('App\Notification', 'object');
    }

    public function logs(){
      return $this->hasMany('App\TaskBranchLog');
    }

    public function quotes(){
        return $this->hasMany('App\TaskBranchQuote');
    }

    public function chatRoom(){
        return $this->morphOne('App\ChatRoom', 'object');
    }

    public static function getUpdateRules(){
        $rules = [
            'status' => ['in:1,2,3,4']
        ];

        return $rules;
    }

    public static function getUpdateMessages(){
        $messages = [
            'status.in' => 'El status no es válido',
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
                    case 'description':
                        //search by the description of the task
                        $query->$where('tasks.description', 'LIKE', '%'.$search.'%');
                        break;
                    case 'id':
                        //search for the address of the branch related to the service
                        $query->$where('tasks.id', '=', $search);
                        break;
                    case 'status':
                        $query->$where('task_branches.status', '=', $search);
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
                            $query->$where('tasks_branches.created_at', '>=', $start);
                        if($end)
                            $query->$where('tasks_branches.created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->$where('tasks_branches.updated_at', '>=', $start);
                        if($end)
                            $query->$where('tasks_branches.updated_at', '<=', $end);
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
                        $query->orderBy('task_branches.created_at', $orderType);
                        break;

                    case 'updated':
                        $query->orderBy('task_branches.updated_at', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}
