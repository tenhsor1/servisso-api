<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
	use SoftDeletes;
    protected $table = 'companies';

	protected $fillable = ['name','description','companiescol','partner_id','category_id'];
	
	protected $hidden = ['deleted_at','created_at','updated_at'];

	public function partner()
    {
        // 1 company is related to one partner
        return $this->belongsTo('App\Partner');
    }

    public function category()
    {
        // 1 company is related to one category
        return $this->belongsTo('App\Category');
    }

    public function branches()
    {
        // 1 company can have multiple branches
        return $this->hasMany('App\Branch');
    }
	
	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages = 
		[
			'required' => ':attribute is required',
			'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];
		
		return $messages;
	}
	
	/**
	* Se obtienen las validaciones del modelo Partner
	*/
	public static function getValidations(){
		$validation = 
			[
				'partner_id' => 'required',
				'name' => 'required|max:59|min:4',
				'description' => 'required|max:499|min:4',
				'category_id' => 'required',
				'companiescol' => 'required'
			];
		
		return $validation;
	}
}
