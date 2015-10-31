<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewComment extends Model
{
    protected $table="news_comments";


    protected $fillable = array('user_id','comment');


    protected $hidden = ['user_type','created_at','updated_at'];

    public function news()
    {
        // 1 comment is related to one new
        return $this->belongsTo('App\News');
    }
}
