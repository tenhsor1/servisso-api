<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
	
	protected $fillable = ['name','description','companiescol','partner_id','category_id'];
	
	//protected $guarded = ['partner_id','category_id'];
}
