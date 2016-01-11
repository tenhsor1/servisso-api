<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'guests';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'
                            , 'created_at'
                            , 'updated_at'
                        ];

    protected $fillable = array('email', 'name', 'phone', 'address', 'zipcode');

    public function services(){
        return $this->morphMany('App\Service', 'userable');
    }

    public static function getMessages(){
        $messages = [
        'required' => ':attribute is required',
        'email' => ':attribute has invalid format',
        'date' => ':attribute should be 10 digits',
        'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
        'digits' => ':attribute should be 10 digits',
        'max' => ':attribute length too long',
        'min' => ':attribute length too short',
        'string' => ':attribute should be characters only'
        ];

        return $messages;
    }

    /**
    * Get validations for storing a new guest
    */
    public static function getStoreValidations(){
        $validation = [
                'email' => 'required|email|max:70|min:11',
                'name' => 'required|max:45|min:4',
                'phone' => 'required|max:20|min:8',
                'address' => 'max:90|min:10',
                'zipcode' => 'max:10|min:4'
            ];

        return $validation;
    }

    /**
    * Get validations for updating a guest information
    */
    public static function getUpdateValidations(){
        $validation = [
                'email' => 'email|max:70|min:11',
                'name' => 'max:45|min:4',
                'phone' => 'max:20|min:8',
                'address' => 'max:90|min:10',
                'zipcode' => 'max:10|min:4'
            ];

        return $validation;
    }
}
