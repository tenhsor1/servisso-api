<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagsBranches extends Model
{
    protected $table="tags_branches";


    protected $fillable = array('tag_id','branch_id');


    protected $hidden = ['created_at','updated_at'];
}