<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
	use SoftDeletes;
    protected $table = 'companies';

	protected $fillable = ['name','description','companiescol','partner_id','category_id'];
	
	protected $hidden = ['deleted_at','created_at','updated_at'];

	public function partner()
    {
        // 1 company is related to one partner
        return $this->belongsTo('App\Partner');
    }

    public function category()
    {
        // 1 company is related to one category
        return $this->belongsTo('App\Category');
    }

    public function branches()
    {
        // 1 company can have multiple branches
        return $this->hasMany('App\Branch');
    }
}
