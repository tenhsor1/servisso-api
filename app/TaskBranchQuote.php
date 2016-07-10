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

    public function taskBranch(){
        return $this->belongsTo('App\TaskBranch');
    }

    public static function getRules(){
        $rules = [
            'description' => ['required','max:500', 'min:15'],
            'task_branch_id' => ['required','exists:task_branches,id'],
            'price' => ['required','numeric'],
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
            'price.numeric' => 'El precio debe de ser un número'
        ];
        return $messages;
    }

}
