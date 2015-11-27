<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
	use SoftDeletes;
    protected $table = "tags";


    protected $fillable = array('name','description','category_id');


    protected $hidden = ['created_at','updated_at','deleted_at','role_id','role'];
	
	public function category()
    {
        // 1 tag is related to one category
        return $this->belongsTo('App\Category');
    }
	
	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages = 
		[
			'required' => ':attribute is required',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];
		
		return $messages;
	}
	
	/**
	* Se obtienen las validaciones del modelo Branch
	*/
	public static function getValidations(){
		$validation = 
			[
				'name' => 'required|max:44|min:4',
				'description' => 'required|max:100|min:4',
				'category_id' => 'required'
			];
		
		return $validation;
	}

}
