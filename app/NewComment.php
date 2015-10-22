<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewComment extends Model
{
    protected $table="news_comments";


    protected $fillable = array('user_id','comment');


    protected $hidden = ['user_type','created_at','updated_at'];
}
