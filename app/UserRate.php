<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRate extends Model
{
	use SoftDeletes;
    protected $table="user_rates";


    protected $fillable = array('service_id','rate','comment');


    protected $hidden = ['partner_id','created_at','updated_at'];
	
	public function service()
    {
        // 1 user rate is related to one service
        return $this->belongsTo('App\Service');
    }
	
	public function partner(){
		//1 user rate is related to one partner
		return $this->belongsTo('App\Partner');
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
				'service_id' => 'required|numeric',
				'rate' => 'required|numeric',
				'partner_id' => 'required|numeric',
			];
		
		return $validation;
	}
}
