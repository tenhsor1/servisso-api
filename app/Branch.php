<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Branch extends Model
{
	
	use SoftDeletes;
    protected $table = 'branches';

	protected $fillable = array('address', 'phone', 'latitude','longitude','schedule','company_id','state_id');
	
	protected $hidden = ['deleted_at','created_at','updated_at','role_id','role'];

	public function company()
    {
        // 1 branch is related to one company
        return $this->belongsTo('App\Company');
    }

    public function services()
    {
        // 1 branch can have multiple services
        return $this->hasMany('App\Service');
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
				'address' => 'required|max:59|min:4',
				'phone' => 'required|max:70|min:10',
				'latitude' => 'required|numeric',
				'longitude' => 'required|numeric',
				'schedule' => 'required|max:99|min:4'
			];
		
		return $validation;
	}
}
