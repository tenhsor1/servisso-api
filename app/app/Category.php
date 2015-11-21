<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  
class Category extends Model
{
	use SoftDeletes;
    protected $table = "categories";


    protected $fillable = ['name','description'];
 

    protected $hidden = ['role_id','role','created_at','updated_at'];
	
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
			'min' => ':attribute length too short'
		];
		
		return $messages;
	}
	
	public static function getValidations(){
		$validation = 
			[  
				'name' => 'required|max:44|min:3',
				'description' => 'required|max:99|min:4',
				'role_id' => '',
				'role' => ''
			];
		
		return $validation;
	}
}
