<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchRate extends Model
{
    use SoftDeletes;
    protected $table="branch_rates";


    protected $fillable = array('rate','comment');


    protected $hidden = ['created_at','updated_at'];

    const BRANCH_RATE_OBJECTS_MAP = ['Task' => 'App\Task'];

    public function object(){
      return $this->morphTo();
    }

    public function branch(){
        //1 user rate is related to one partner
        return $this->belongsTo('App\Branch');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public static function getRules(){
        $rules = [
            'rate' => ['required', 'numeric', 'min:1', 'max:5'],
            'type' => ['required', 'in:task,other'],
            'description' => ['min:15', 'max:500'],
        ];

        return $rules;
    }

    public static function getMessages(){
        $messages = [
            'rate.max' => 'La calificación debe ser máximo :max',
            'rate.min' => 'La calificación debe de ser mínimo :min',
            'description.max' => 'Descripción debe tener máximo :max caracteres',
            'description.min' => 'Descripción debe tener minimo :min caracteres',
            'user_id.required' => 'El usuario es obligatorio',
            'user_id.exists' => 'El usuario no existe',
            'branch_id.required' => 'El usuario es obligatorio',
            'branch_id.exists' => 'La sucursal no existe',
            'type.required' => 'El tipo de calificación es requerida',
            'type.in' => 'El tipo de calificación no es válida',
            'task_branch_id.required' => 'La relación tarea sucursal es obligatoria',
            'task_branch_id.exists' => 'la relación tarea sucursal no existe',
        ];

        return $messages;
    }
}