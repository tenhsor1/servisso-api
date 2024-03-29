<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AWS;

class TaskImageHidden extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'task_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['image', 'thumbnail'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function task()
    {
        // 1 admin can have one country
        return $this->belongsTo('App\Task');
    }

    public static function getMessages(){
        $messages =
        [
            'image.required' => 'La ruta de la imagen es obligatoria',
            'thumbnail.required' => 'La ruta del thumbnail es obligatoria',
            'service_id.required' => 'El ID del servicio es requerido',
        ];
        return $messages;
    }

    public static function getRules(){
        $rules = array(
                'image' => ['required'],
                'thumbnail' => ['required'],
                'service_id' => ['required'],
            );

        return $rules;
    }
}
