<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
	
	protected $fillable = array('address', 'phone', 'latitude','longitude','schedule','company_id','state_id');
	
	//protected $guarded = ['company_id','state_id'];
}
