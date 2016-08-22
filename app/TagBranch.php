<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagBranch extends Model
{
    protected $table="tags_branches";


    protected $fillable = array('tag_id','branch_id');


    protected $hidden = ['created_at','updated_at'];

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages =
		[
			'required' => ':attribute is required',
			'numeric' => ':attribute should be a number'
		];

		return $messages;
	}

	/**
	* Se obtienen las validaciones del modelo Branch
	*/
	public static function getValidations(){
		$validation =
			[
				'tag_id' => 'required|numeric',
				'branch_id' => 'required|numeric',
			];

		return $validation;
	}

}