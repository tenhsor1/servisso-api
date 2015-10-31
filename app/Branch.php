<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Branch extends Model
{
	
	use SoftDeletes;
    protected $table = 'branches';

	protected $fillable = array('address', 'phone', 'latitude','longitude','schedule','company_id','state_id');
	
	protected $hidden = ['deleted_at','created_at','updated_at'];

	public function company()
    {
        // 1 branch is related to one company
        return $this->belongsTo('App\Company');
    }

    public function services()
    {
        // 1 branch can have multiple services
        return $this->hasMany('App\Service');
    }
}
