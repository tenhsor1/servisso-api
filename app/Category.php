<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
	use SoftDeletes;
    protected $table = "categories";


    protected $fillable = array('name','description');


<<<<<<< HEAD
    protected $hidden = ['role_id','role','created_at','updated_at','deleted_at'];
=======
    protected $hidden = ['created_at','updated_at','deleted_at'];
>>>>>>> upstream/develop
	
	public function tags()
    {
        // 1 category can have multiple tags
        return $this->hasMany('App\Tag');
    }
<<<<<<< HEAD


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
            'name' => 'required|max:45|min:5',
            'description' => 'required|max:500|min:7',
            'role_id' => '',
            'role' => ''
        ];


        return $validation;
    }
=======
	
	public static function getMessages(){
		$messages = 
		[
			'required' => ':attribute is required',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];
		
		return $messages;
	}
	
	public static function getValidations(){
		$validation = 
			[
				'name' => 'required|max:44|min:3',
				'description' => 'required|max:99|min:4',
			];
		
		return $validation;
	}
>>>>>>> upstream/develop
}
