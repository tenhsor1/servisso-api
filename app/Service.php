<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['service_id'
                            , 'branch_id'
                            , 'user_id'
                            , 'user_type'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['service_id'
                            , 'branch_id'
                            , 'user_id'
                            , 'user_type'
                        ];

    public function branch(){
        //1 service is related to one branch
        return $this->belongsTo('App\Branch');
    }
}
