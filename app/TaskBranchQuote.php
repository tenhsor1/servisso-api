<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskBranchQuote extends ServissoModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'task_branch_quotes';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['task_branch_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
                            'deleted_at'
                        ];

    public static function boot()
    {
        TaskBranchQuote::created(function ($taskBranchQuote) {
            $taskBranchQuote->addNotification('NEW');
        });
    }

    public function taskBranch(){
        return $this->belongsTo('App\TaskBranch');
    }

    public function addNotification($verb){

        $receiver = $this->getUserCreated();
        $sender = $this->getOwnerBranch();

        $notification = new Notification;
        $notification->receiver_id = $receiver->id;
        $notification->object_id = $this->id;
        $notification->object_type = Notification::NOTIFICATION_OBJECTS_MAP['TaskBranchQuote'];
        $notification->sender_id = $sender->user_id;
        $notification->sender_type = Notification::USER_RELATION;
        $notification->verb = $verb;
        $notification->extra = json_encode([
            'task' => $this->taskBranch->task->toArray(),
            'branch' => $sender
        ]);
        $notification->save();
    }

    public function getUserCreated(){
      return $this->select('users.id')
            ->join('task_branches','task_branches.id','=','task_branch_quotes.task_branch_id')
            ->join('tasks','tasks.id','=','task_branches.task_id')
            ->join('users','users.id','=','tasks.user_id')
            ->where('task_branches.id', $this->task_branch_id)
            ->first();
    }

    public function getOwnerBranch(){
      return $this->select('users.id AS user_id', 'companies.name AS company_name')
            ->join('task_branches','task_branches.id','=','task_branch_quotes.task_branch_id')
            ->join('branches','branches.id','=','task_branches.branch_id')
            ->join('companies','companies.id','=','branches.company_id')
            ->join('users','users.id','=','companies.user_id')
            ->where('task_branch_quotes.id', $this->id)
            ->first();
    }

    public static function getRules(){
        $rules = [
            'description' => ['required','max:500', 'min:15'],
            'task_branch_id' => ['required','exists:task_branches,id'],
            'price' => ['required','numeric','between:0,10000000.99'],
			'date' => ['required','date_format:Y-m-d H:i:s', 'after:yesterday']
        ];
        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'description.required' => 'Descripción es obligatoria',
            'description.max' => 'Descripción debe tener máximo :max caracteres',
            'description.min' => 'Descripción debe tener minimo :min caracteres',
            'task_branch_id.required' => 'La tarea por sucursal es obligatoria',
            'task_branch_id.exists' => 'La tarea para la sucursal no existe',
            'price.required' => 'El precio es obligatorio',
            'price.numeric' => 'El precio debe de ser un número',
            'price.between' => 'El precio maximo de cotizacion es $10,000,000',
			'date.required' => 'la fecha es obligatoria',
            'date.date_format' => 'la fecha debe de ser en formato Y-m-d H:i:s',
            'date.after' => 'la fecha no puede ser anterior a hoy'
        ];
        return $messages;
    }

}
