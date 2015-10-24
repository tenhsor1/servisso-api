<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [   'service_id'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['service_id'];

    public function service(){
        //1 sms is related to one service
        return $this->belongsTo('App\Service');
    }
}
