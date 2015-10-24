<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'partners';

	protected $fillable = array('email', 'password', 'name','longitude','lastname',
								'birthdate','phone','address','zipcode','status','state_id','country_id','plan_id');

	//protected $guarded = ['state_id','country_id','plan_id'];

	protected $hidden = ['password'];

    public function companies(){
        //1 partner can have multiple companies
        return $this->hasMany('App\Company');
    }
}
