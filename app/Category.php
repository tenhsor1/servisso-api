<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
	use SoftDeletes;
    protected $table = "categories";


    protected $fillable = array('name','description');


    protected $hidden = ['created_at','updated_at'];
	
	public function tags()
    {
        // 1 category can have multiple tags
        return $this->hasMany('App\Tag');
    }
}
