<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table="admins";


    protected $fillable = array('email','name','lastname','addres','phone','zipcode');


    protected $hidden = ['password','state_id','country_id','role_id','created_at','updated_at'];

    public function news()
    {
        // 1 admin can have multiple news
        return $this->hasMany('App\News');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }
}
