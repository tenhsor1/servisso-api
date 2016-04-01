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
	
	public static function getRules(){
		$rules = [
			'email' => ['required','email'],
			'name' => ['required','min:2','max:30'],
			'address' => ['max:150'],
			'phone' => ['max:30'],
			'zipcode' => ['max:8']
		];
		
		return $rules;
	}

    public static function getMessages(){
        $messages = [
			'email.required' => 'Email es obligatorio',
            'email.email' => 'Email no válido',
			'name.required' => 'Nombre es obligatorio',
			'name.min' => 'Nombre debe tener minimo :min caracteres',
			'name.max' => 'Nombre debe tener máximo :max caracteres',
			'address.max' => 'Dirección debe tener máximo :max caracteres',
			'phone.max' => 'Teléfono debe tener máximo :max caracteres',
			'zipcode.max' => 'Código postal debe tener máximo :max caracteres'
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
