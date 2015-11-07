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
                            , 'deleted_at'
                            , 'created_at'
                            , 'updated_at'
                        ];

    public function branch(){
        //1 service is related to one branch
        return $this->belongsTo('App\Branch');
    }
	
	public function userRate(){
		//1 sevice is related to one user rate
		return $this->hasOne('App\UserRate');
	}
	
	public function partnerRate(){
		//1 service rate is related to one partner rate
		return $this->hasOne('App\PartnerRate');
	}
}
