<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
	use SoftDeletes;
    protected $table = "categories";


    protected $fillable = array('name','description');


    protected $hidden = ['created_at','updated_at','deleted_at'];
	
	public function tags()
    {
        // 1 category can have multiple tags
        return $this->hasMany('App\Tag');
    }
	
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
}
