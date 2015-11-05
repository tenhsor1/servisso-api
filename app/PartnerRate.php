<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerRate extends Model
{
	use SoftDeletes;
    protected $table="partner_rates";


    protected $fillable = array('service_id','rate','comment');


    protected $hidden = ['created_at','updated_at'];
	
	public function service()
    {
        // 1 user rate is related to one service
        return $this->belongsTo('App\Service');
    }

}
