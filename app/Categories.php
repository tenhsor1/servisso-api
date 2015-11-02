<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table="categories";


    protected $fillable = array('name','description');


    protected $hidden = ['created_at','updated_at'];
}
