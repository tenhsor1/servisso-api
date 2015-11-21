<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Admin extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use SoftDeletes;
    use Authenticatable, Authorizable, CanResetPassword;
    protected $table="admins";


    protected $fillable = array('email','name','lastname','addres','phone','zipcode'); 


    protected $hidden = ['password','state_id','country_id','update_id','role_id','created_at','updated_at','deleted_at '];

    public function news()
    {
        // 1 admin can have multiple news
        return $this->hasMany('App\News');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Se obtienen los mensajes de errores
     */
    public static function getMessages(){
        $messages = [
            'required' => ':attribute is required',
            'email' => ':attribute has invalid format',
            'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
            'digits' => ':attribute should be 10 digits',
            'max' => ':attribute length too long',
            'min' => ':attribute length too short',
            'string' => ':attribute should be characters only'
        ];

        return $messages;
    }

    /**
     * Se obtienen las validaciones del modelo Partner
     */
    public static function getValidations(){
        $validation = [
            'email' => 'required|email|max:70|min:11',
            'password' => 'required|max:99|min:7',
            'name' => 'required|max:45|min:4',
            'last_name' => 'max:45|min:4',
            'address' => 'required|max:90|min:10',
            'phone' => 'required|digits:10|max:20|min:10',
            'zipcode' => 'max:10|min:4',
            'state_id' => '',
            'country_id' => '',
            'role_id' => '',
			'update_id' => ''
        ];

        return $validation;
    }

}
