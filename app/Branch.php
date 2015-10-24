<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';

	protected $fillable = array('address', 'phone', 'latitude','longitude','schedule','company_id','state_id');

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
