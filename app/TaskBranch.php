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
    }

    public function addLog($type){
        $log = new TaskBranchLog(['type' => $type]);
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
            'task' => $this->task->toArray()
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

    public function task(){
        //1 task-branch has one task
        return $this->belongsTo('App\Task');
    }

    public function branch(){
        //a task has 1 category
        return $this->belongsTo('App\Branch');
    }

    public function notifications(){
        return $this->morphMany('App\TaskBranch', 'object');
    }

    public function logs(){
      return $this->hasMany('App\TaskBranchLog');
    }
}
