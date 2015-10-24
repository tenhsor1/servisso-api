<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

	protected $fillable = ['name','description','companiescol','partner_id','category_id'];

	public function service()
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
