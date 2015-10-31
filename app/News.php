<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table="news";


    protected $fillable = array('admin_id','title','content','image');


    protected $hidden = ['status','created_at','updated_at'];

    public function admin()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Admin');
    }

    public function comments()
    {
        // 1 new can have multiple comments
        return $this->hasMany('App\NewComment');
    }
}
