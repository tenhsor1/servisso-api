<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table="news";


    protected $fillable = array('admin_id','title','content','image');


    protected $hidden = ['status','created_at','updated_at'];
}
