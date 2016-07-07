<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use App\Notification;

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
}
