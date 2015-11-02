<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerRate extends Model
{
    protected $table="partner_rates";


    protected $fillable = array('service_id','rate','comment');


    protected $hidden = ['created_at','updated_at'];
}
