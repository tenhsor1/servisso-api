<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $table="tags";


    protected $fillable = array('name','description');


    protected $hidden = ['category_id','created_at','updated_at'];
}
