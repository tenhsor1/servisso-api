<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRates extends Model
{
    protected $table="user_rates";


    protected $fillable = array('service_id','rate','comment');


    protected $hidden = ['partner_id','created_at','updated_at'];
}
